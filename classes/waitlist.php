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
 * Waiting list implementation.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Waiting list implementation
  */
class enrol_bycategory_waitlist {

    /** @var string */
    private $tablename = 'enrol_bycategory_waitlist';
    /** @var int */
    private $instanceid;

    /**
     * Initialize waiting list instance
     * @param int $instanceid id of the enrol instance
     */
    public function __construct($instanceid) {
        global $DB;

        if (empty($instanceid)) {
            throw new coding_exception('$instanceid is empty');
        }

        $this->instanceid = $instanceid;
    }

    /**
     * Return number of users on the waiting list
     * @return int
     */
    public function get_count() {
        global $DB;

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);

        return $count;
    }

    /**
     * Remove a user from the waiting list by its id
     * @param int $userid
     */
    public function remove_user($userid) {
        global $DB;

        if (empty($userid)) {
            throw new coding_exception('$userid is empty empty');
        }

        $DB->delete_records($this->tablename, ['instanceid' => $this->instanceid, 'userid' => $userid]);
    }

    /**
     * Remove multiple users from the waiting list
     * @param array $userids Array of userids to remove from the waiting list
     */
    public function remove_users($userids) {
        global $DB;

        if (false === is_array($userids) || 0 === count($userids)) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($userids);
        array_push($inparams, $this->instanceid);

        $DB->delete_records_select($this->tablename,
            "userid {$insql} and instanceid = ?",
            $inparams
        );
    }

    /**
     * Add a user to the waiting list
     * @param int $userid
     * @return int Id of the created record
     */
    public function add_user($userid) {
        global $DB, $USER;

        if (empty($userid)) {
            throw new coding_exception('$userid is empty');
        }

        // get seniorty date from an external service if service is provided
        $externaluserinfo = get_config('enrol_bycategory', 'externalseniorityapi');
        $seniority = \core_user::get_user($userid)->timecreated;
        if (!empty($externaluserinfo)) {
            if (!empty($seniority = self::call_external_service($externaluserinfo, $userid))) {
                $dtime = DateTime::createFromFormat("Y-m-d G:i:s", $seniority);
                $seniority = !empty($dtime) ? $dtime->getTimestamp() : 0;
            }
        }

        $now = time();

        $params = [
            'userid' => $userid,
            'instanceid' => $this->instanceid,
            'usermodified' => $USER->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'senioritydate' => $seniority,
        ];

        return $DB->insert_record($this->tablename, $params, true, false);
    }

    /**
     * Check if the given userid is on the waiting list
     * @param int $userid
     * @return bool
     */
    public function is_on_waitlist($userid) {
        global $DB;

        if (empty($userid)) {
            throw new coding_exception('$userid is empty');
        }

        return $DB->record_exists($this->tablename, ['userid' => $userid, 'instanceid' => $this->instanceid]);
    }

    /**
     * Check of a given list of users is present on the waiting list
     * @param array $userids array of userids
     * @return array ['intersect' => userids[], 'diff' => userids[]]
     */
    public function is_on_waitlist_bulk($userids) {
        global $DB;

        list ($insql, $inparams) = $DB->get_in_or_equal($userids);
        array_push($inparams, $this->instanceid);

        $sql = "SELECT userid FROM {{$this->tablename}} WHERE userid $insql AND instanceid = ?";
        $existingusers = $DB->get_records_sql($sql, $inparams);
        $existingusers = array_keys($existingusers);

        return [
            'onwaitlist' => array_intersect($userids, $existingusers),
            'missing' => array_values(array_diff($userids, $existingusers)),
        ];
    }

    /**
     * Return the position of the user on the waiting list
     * @param int $userid
     * @return int position of the user or -1 if user is not on list
     */
    public function get_user_position($userid) {
        global $DB;

        if (empty($userid)) {
            throw new coding_exception('$userid is empty');
        }

        $usernotifylimit = get_config('enrol_bycategory', 'waitlistnotifylimit');
        if ($usernotifylimit === false) {
            $usernotifylimit = 5;
        }

        $sql = "SELECT userid FROM {enrol_bycategory_waitlist}
                 WHERE instanceid = :instanceid AND notified < :notifylimit
                 ORDER BY timecreated ASC";

        $waitlistusers = $DB->get_records_sql($sql, [
            'instanceid' => $this->instanceid,
            'notifylimit' => $usernotifylimit,
        ]);

        $userids = array_keys($waitlistusers);
        $userpos = array_search($userid, $userids);

        return $userpos !== false ? $userpos + 1 : -1;
    }

    /**
     * Checks if user can enrol.
     *
     * @param stdClass $instance enrolment instance
     * @param int $userid id of the user trying to enrol
     * @param bool $ignorewaitlist if true will ignore if users are still on the waiting list.
     *             This is used when a user is trying to enrol from the waiting list.
     *             There should be at least one spot available.
     * @return bool|string true if successful, else error message or false.
     */
    public function can_enrol(stdClass $instance = null, $userid = null, $ignorewaitlist = false) {
        global $DB, $USER;

        if ($instance === null) {
            $instance = $DB->get_record('enrol', ['id' => $this->instanceid], '*', MUST_EXIST);
        }

        if ($userid === null) {
            $userid = $USER->id;
        }

        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return get_string('canntenrol', 'enrol_bycategory');
        }

        // Check if user has the capability to enrol in this context.
        if (!has_capability('enrol/bycategory:enrolself', context_course::instance($instance->courseid))) {
            return get_string('canntenrol', 'enrol_bycategory');
        }

        if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
            return get_string('canntenrolearly', 'enrol_bycategory', userdate($instance->enrolstartdate));
        }

        /*
         * User can enrol if $ingorewaitlist is true even if the enrolment is already closed
         * or enrolment is not allowed.
         */
        if ($ignorewaitlist === false) {

            if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
                return get_string('canntenrollate', 'enrol_bycategory', userdate($instance->enrolenddate));
            }

            if (!$instance->customint6) {
                // New enrols not allowed.
                return get_string('canntenrol', 'enrol_bycategory');
            }
        }

        if ($DB->record_exists('user_enrolments', ['userid' => $userid, 'enrolid' => $instance->id])) {
            return get_string('canntenrol', 'enrol_bycategory');
        }

        if ($instance->customint1 > 0) {
            // Has successfully finished course in specified category.
            $categoryid = $instance->customint1;
            $timesincecompletion = '';
            // If time since completion is set.
            if ($instance->customint5 > 0 && $ignorewaitlist === false) {
                // ... by default count back from now.
                $startdate = $this->start_of_day_timestamp(time());

                if ($instance->customchar1 == 1 && $instance->enrolstartdate) {
                    $startdate = $this->start_of_day_timestamp($instance->enrolstartdate);
                }

                $timelimit = $startdate - $instance->customint5;
                $timesincecompletion = ' AND cc.timecompleted > ' . $timelimit;
            }

            $sql = 'SELECT count(c.id) FROM {user} u
                    JOIN {user_enrolments} ue on u.id = ue.userid
                    JOIN {enrol} e ON e.id = ue.enrolid
                    JOIN {course} c ON (e.courseid = c.id
                        AND c.category = :categoryid)
                    JOIN {course_completions} cc ON (cc.course = c.id
                        AND cc.userid = u.id
                        AND cc.timecompleted IS NOT NULL' . $timesincecompletion . ')
                WHERE u.id = :userid';

            $params = [
                'userid' => $userid,
                'categoryid' => $categoryid,
            ];

            $count = $DB->count_records_sql($sql, $params);
            if ($count == 0) {
                $category = \core_course_category::get($categoryid, MUST_EXIST, true, $userid);

                $categorylink = html_writer::link(
                    new moodle_url('/course/index.php', ['categoryid' => $categoryid]),
                    $category->name
                );

                if ($instance->customint5 && $ignorewaitlist === false) {
                    // No course completed in specified category since time x.
                    return get_string('nocourseincategorysince', 'enrol_bycategory', $categorylink);
                }
                // No course completed in specified category without timelimit.
                return get_string('nocourseincategory', 'enrol_bycategory', $categorylink);
            }
        }

        if ($instance->customint3 > 0) {
            // Max enrol limit specified.
            $count = $DB->count_records('user_enrolments', ['enrolid' => $instance->id]);
            if ($count >= $instance->customint3) {
                // Bad luck, no more self enrolments here.
                return get_string('maxenrolledreached', 'enrol_bycategory');
            }

            // Empty spaces available and waiting list is enabled.
            if (1 == $instance->customchar2 && false === $ignorewaitlist) {
                $waitlist = new enrol_bycategory_waitlist($instance->id);
                $waitlistcount = $waitlist->get_count();
                if ($waitlistcount > 0) {
                    // Users on the waiting list have to be enroled first before self enrolment becomes available again.
                    return get_string('maxenrolledreached', 'enrol_bycategory');
                }
            }
        }

        // evaluate for external criteria which returns an array with whether the user is eligible or not, and a message
        if (!empty($instance->customchar3)) {
            $eligibilityjson = self::call_external_service($instance->customchar3, $userid);
            $eligibility = json_decode($eligibilityjson);
            if (!empty($eligibility)) {
                if ($eligibility->eligible) {
                    return true;
                }
                else {
                    return $eligibility->warning;
                }
            } else {
                return get_string('unabletogetcriteria', 'enrol_bycategory');
            }
        }

        return true;
    }

    /**
     * Reset the notified counter of a specific user and waiting list
     * @param int $userid
     */
    public function reset_notification_counter($userid) {
        global $DB;
        $DB->set_field($this->tablename, 'notified', 0, ['instanceid' => $this->instanceid, 'userid' => $userid]);
    }

    /**
     * Select courses with available space
     *
     * @return array Map with enrol ids as keys
     */
    public static function select_courses_with_available_space() {
        global $DB;

        $sql = "SELECT e.id AS instanceid, c.id, c.fullname, c.shortname FROM {enrol} e
                  JOIN {course} c ON e.courseid = c.id
                 WHERE e.enrol = :pluginname
                    AND e.status = :status
                    AND e.customint6 = 1
                    AND (
                        e.customint3 = 0
                        OR (
                            e.customint3 > (
                                SELECT COUNT(id) FROM {user_enrolments} ue WHERE ue.enrolid = e.id
                            )
                        )
                    )
                    AND (
                        e.enrolstartdate = 0
                        OR e.enrolstartdate < :startbefore
                    )
                    AND (
                        e.enrolenddate = 0
                        OR e.enrolenddate > :endafter
                    )";

        $now = time();
        $params = [
            'pluginname' => 'bycategory',
            // Has to be two different variables.
            'startbefore' => $now,
            'endafter' => $now,
            'status' => ENROL_INSTANCE_ENABLED,
        ];

        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }

    /**
     * Select users to be notified based on an array of enrolids
     *
     * @param array $enrolids array of enrolids
     *
     * @return array Map of enrol_bycategory_waitlist results
     */
    public static function select_users_from_waitlist_for_notification($enrolids) {
        global $DB;

        $usernotifycount = get_config('enrol_bycategory', 'waitlistnotifycount');
        if ($usernotifycount === false) {
            $usernotifycount = 5;
        }
        $userstonotify = $usernotifycount;

        $usernotifylimit = get_config('enrol_bycategory', 'waitlistnotifylimit');
        if ($usernotifylimit === false) {
            $usernotifylimit = 5;
        }

        $nextreminderdays = get_config('enrol_bycategory', 'waitlistnotifyperiod');
        if ($nextreminderdays === false) {
            $nextreminderdays = 3;
        }

        $waitlistentries = [];

        foreach ($enrolids as $enrolid) {
            $instance = $DB->get_record('enrol', ['id' => $enrolid], '*', MUST_EXIST);

            // check if notify count is based on specified settings or available spots (0)
            if ($usernotifycount == 0) {
                $enroledcount = $DB->count_records('user_enrolments', ['enrolid' => $instance->id]);
                $userstonotify = $instance->customint3 >= $enroledcount ? $instance->customint3 - $enroledcount : 0;
            }

            if ($userstonotify > 0) {
                // sort by seniority when applicable
                $sortcolumn = !empty($instance->customint8) ? 'senioritydate' : 'timecreated';
                $sql = "WITH waitlist_window as (
                            SELECT *, ROW_NUMBER() OVER (PARTITION BY instanceid ORDER BY $sortcolumn ASC) r
                            FROM {enrol_bycategory_waitlist}
                            WHERE instanceid = $enrolid
                                AND notified < :notifylimit
                                AND (DATEDIFF(CURDATE(), FROM_UNIXTIME(timemodified)) > :nextreminder OR notified > 0)
                        )
                        SELECT * FROM waitlist_window WHERE r <= :useramount";

                $entries = $DB->get_records_sql($sql, [
                    'sortcolumn'  => $sortcolumn,
                    'useramount'  => $userstonotify,
                    'notifylimit' => $usernotifylimit,
                    'nextreminder'=> $nextreminderdays,
                ]);
                $waitlistentries = $waitlistentries + $entries;
            }
        }

        return $waitlistentries;
    }

    /**
     * Increase notified field based on an array of enrol_bycategory_waitlist ids
     *
     * @param array $waitlistids Array of enrol_bycategory_waitlist ids
     *
     * @return mixed|null
     */
    public static function increase_notified($waitlistids) {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal($waitlistids, SQL_PARAMS_NAMED);
        $sql = "UPDATE {enrol_bycategory_waitlist}
                   SET notified = notified + 1, timemodified = :now
                 WHERE id $insql";

        $result = $DB->execute($sql, [
            'now' => time(),
        ] + $inparams);

        return $result;
    }

    /**
     * Get a timestamp for start of day
     * @param int $timestamp
     * @return int
     */
    private function start_of_day_timestamp($timestamp) {
        $startofday = new DateTime();
        $startofday->setTimestamp($timestamp);
        $startofday->setTime(0, 0, 0, 0);

        return $startofday->getTimestamp();
    }

    /**
     * Get the user's seniority.
     *
     * @param  string $url    The service URL and query parameters to evaluate
     * @param  int    $userid The user id to evaluate in the external service
     * @return string $value  The timezone seniority.
     */
	public static function call_external_service(string $url, int $userid) {

        $url = self::parse_text($url, \core_user::get_user($userid));
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('googletimezoneerror', 'local_booking'));

		return $data;
	}

    /**
     * Return an array of valid options for the status.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param  string $text text containing keys to replace
     * @param  object $a    object containing key/value pairs
     * @return string
     */
    public static function parse_text(string $text, object $a) {
        $mergedtext = '';
        $options = (array) $a;
        $search = array();
        $replace = array();
        foreach ($options as $key => $value) {
            $search[]  = '{$a->'.$key.'}';
            $replace[] = (string)$value;
        }
        if ($search) {
            $mergedtext = str_replace($search, $replace, $text);
        }
        return $mergedtext;
    }
}
