<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Privacy Subsystem implementation for enrol_bycategory.
 *
 * @package     enrol_bycategory
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_bycategory\privacy;

use context;
use context_course;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\content_writer;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for enrol_bycategory implementing null_provider.
 *
 * @package     enrol_bycategory
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @author      Matthias Tylkowski
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection     $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'enrol_bycategory_waitlist',
            [
                'userid' => 'privacy:metadata:enrol_bycategory_waitlist:userid',
            ],
            'privacy:metadata:enrol_bycategory_waitlist'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT c.id
                  FROM {enrol_bycategory_waitlist} ebw
                  JOIN {enrol} e on e.id = ebw.instanceid
                  JOIN {context} c ON c.contextlevel = :context AND c.instanceid = e.courseid
                 WHERE ebw.userid = :userid";

        $params = [
            'context' => CONTEXT_COURSE,
            'userid' => $userid,
        ];

        $contextlist = new contextlist();
        $contextlist->set_component('enrol_bycategory');
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof context_course) {
            return;
        }

        $sql = "SELECT u.id
                  FROM {enrol_bycategory_waitlist} ebw
                  JOIN {enrol} e ON ebw.instanceid = e.id
                  JOIN {user} u ON ebw.userid = u.id
                 WHERE e.courseid = :courseid";

        $params = ['courseid' => $context->instanceid];

        $userlist->add_from_sql('id', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT ebw.*, e.courseid
                  FROM {enrol_bycategory_waitlist} ebw
                  JOIN {enrol} e ON ebw.instanceid = e.id
                  JOIN {context} c ON e.courseid = c.instanceid AND c.contextlevel = :context
                  JOIN {user} u ON u.id = ebw.userid
                 WHERE c.id {$contextsql} AND u.id = :userid
              ORDER BY e.courseid";

        $params = [
            'context' => CONTEXT_COURSE,
            'userid' => $user->id,
        ] + $contextparams;

        $enrolments = $DB->get_recordset_sql($sql, $params);
        $enrolmentdata = [];

        foreach ($enrolments as $enrolment) {
            $enrolment->timecreated = transform::datetime($enrolment->timecreated);
            $enrolment->timemodified = transform::datetime($enrolment->timemodified);
            $enrolmentdata[$enrolment->courseid][] = $enrolment;
        }
        $enrolments->close();

        $subcontext = \core_enrol\privacy\provider::get_subcontext([get_string('pluginname', 'enrol_bycategory')]);
        foreach ($enrolmentdata as $courseid => $enrolments) {
            $data = (object) [
                'waitlists' => $enrolments,
            ];

            writer::with_context(\context_course::instance($courseid))->export_data($subcontext, $data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if (!$context instanceof context_course) {
            return;
        }

        $enrolids = $DB->get_fieldset_select(
            'enrol', 'id',
            'courseid = :courseid AND enrol = :enrol',
            [
                'courseid' => $context->instanceid,
                'enrol' => 'bycategory',
            ]
        );
        list($insql, $inparams) = $DB->get_in_or_equal($enrolids, SQL_PARAMS_NAMED);
        $select = "instanceid $insql";
        $DB->delete_records_select('enrol_bycategory_waitlist', $select, $inparams);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $contexts = $contextlist->get_contexts();
        $courseids = [];

        foreach ($contexts as $context) {
            if ($context instanceof context_course) {
                $courseids[] = $context->instanceid;
            }
        }
        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $enrolids = $DB->get_fieldset_select(
            'enrol',
            'id',
            "courseid $coursesql AND enrol = :enrol",
            [
                'enrol' => 'bycategory',
            ] + $courseparams
        );
        list($insql, $inparams) = $DB->get_in_or_equal($enrolids, SQL_PARAMS_NAMED);
        $select = "userid = :userid AND instanceid $insql";
        $params = $inparams + ['userid' => $user->id];

        $DB->delete_records_select('enrol_bycategory_waitlist', $select, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();

        $enrolids = $DB->get_fieldset_select(
            'enrol', 'id',
            'courseid = :courseid AND enrol = :enrol',
            [
                'courseid' => $context->instanceid,
                'enrol' => 'bycategory',
            ]
        );

        list($enrolsql, $enrolparams) = $DB->get_in_or_equal($enrolids, SQL_PARAMS_NAMED);
        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = $enrolparams + $userparams;

        $select = "instanceid $enrolsql AND userid $usersql";
        $DB->delete_records_select('enrol_bycategory_waitlist', $select, $params);
    }
}
