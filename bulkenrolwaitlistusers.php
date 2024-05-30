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
 * Bulk enrol a users from the waiting list into a course manually.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

defined('MOODLE_INTERNAL') || die();

$enrolid = required_param('enrolid', PARAM_INT);
$userids = required_param_array('userids', PARAM_INT);
$targetcourseid = required_param('targetcourseid', PARAM_INT);
$targetenrolid = required_param('targetenrolid', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

$instance = $DB->get_record('enrol', ['id' => $enrolid, 'enrol' => 'bycategory'], '*', MUST_EXIST);
$course = get_course($instance->courseid);
$targetcourse = get_course($targetcourseid);
$context = context_course::instance($course->id, MUST_EXIST);

$PAGE->set_url('/enrol/bycategory/bulkenrolwaitlistusers.php');
require_login($course);
require_capability('enrol/bycategory:manage', $context);

$waitlist = new enrol_bycategory_waitlist($instance->id);

$mapusers = function ($user) {
    return '__' . fullname($user) . '__';
};

$returnurl = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $instance->id]);

if ($confirm && confirm_sesskey()) {
    $onwaitlistresult = $waitlist->is_on_waitlist_bulk($userids);
    $onwaitlistuserids = $onwaitlistresult['onwaitlist'];
    $missinguserids = $onwaitlistresult['missing'];

    $enrolinstances = enrol_get_instances($targetcourse->id, true);
    $targetenrolinstance = null;
    foreach ($enrolinstances as $enrolinstance) {
        if ($enrolinstance->id == $targetenrolid ) {
            $targetenrolinstance = $enrolinstance;
            break;
        }
    }

    if ($targetenrolinstance === null) {
        redirect($returnurl, get_string('enrolmentmissing', 'enrol_bycategory'), null, notification::NOTIFY_ERROR);
    }

    $enrol = enrol_get_plugin($targetenrolinstance->enrol);
    if ($enrol instanceof enrol_bycategory_plugin) {
        foreach ($onwaitlistuserids as $userid) {
            $enrolresult = $enrol->enrol_user_manually($targetenrolinstance, $userid);
            if ($enrolresult === true) {
                $waitlist->remove_user($userid);
            }
        }
    } else {
        foreach ($onwaitlistuserids as $userid) {
            $enrol->enrol_user($targetenrolinstance, $userid);
            $waitlist->remove_user($userid);
        }
    }

    $targeturl = new moodle_url('/user/index.php', ['id' => $targetcourseid]);
    if (count($missinguserids) > 0) {
        $a = markdown_to_html(implode(",  \n", array_map($mapusers, $missinguserids)));
        redirect(
            $targeturl,
            get_string('bulkenrolusersmissing', 'enrol_bycategory', $a)
        );
    }

    redirect($targeturl, get_string('bulkenrolsuccess', 'enrol_bycategory'), null, notification::NOTIFY_SUCCESS);
}

$users = enrol_bycategory_get_users_by_id($userids);

$enrolinstance = $DB->get_record('enrol', ['id' => $targetenrolid], '*', MUST_EXIST);
$enrolmentname = empty($enrolinstance->name) ? get_string('pluginname', 'enrol_' . $enrolinstance->enrol) : $enrolinstance->name;

$confirmmessage = get_string('bulkenrolconfirmmessage', 'enrol_bycategory', [
    // ... <space><space>\n = markdown line break.
    'users' => implode(",  \n", array_map($mapusers, $users)),
    'coursename' => '__' . $targetcourse->fullname . '__',
    'enrol' => '__' . $enrolmentname . '__',
]);

$params = [
    'confirm' => 1,
    'sesskey' => sesskey(),
    'enrolid' => $enrolid,
    'targetcourseid' => $targetcourseid,
    'targetenrolid' => $targetenrolid,
];

for ($i = 0; $i < count($userids); $i++) {
    $params["userids[{$i}]"] = $userids[$i];
}

$yesurl = new moodle_url($PAGE->url, $params);

$title = get_string('bulkenrolconfirmtitle', 'enrol_bycategory');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('users'));
$PAGE->navbar->add(get_string('enrolmentinstances', 'enrol'), new moodle_url('/enrol/instances.php', ['id' => $course->id]));
$PAGE->navbar->add(get_string('waitlist', 'enrol_bycategory'), $returnurl);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->confirm(markdown_to_html($confirmmessage), $yesurl, $returnurl);
echo $OUTPUT->footer();

