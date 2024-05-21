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
 * Enrol a user via Notification with token
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/enrol/bycategory/locallib.php');

defined('MOODLE_INTERNAL') || die();

require_login();
$token = required_param('token', PARAM_TEXT);
$dashboardurl = new moodle_url('/my');
$tokentablename = 'enrol_bycategory_token';

$tokenrecord = $DB->get_record($tokentablename, ['token' => $token], '*', MUST_EXIST);
$waitlistrecord = $DB->get_record('enrol_bycategory_waitlist', ['id' => $tokenrecord->waitlistid], '*', MUST_EXIST);
$userid = $waitlistrecord->userid;
$instanceid = $waitlistrecord->instanceid;
$waitlisturl = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $instanceid]);
$waitlist = new enrol_bycategory_waitlist($instanceid);

// Token is not for the current user.
if ($userid !== $USER->id) {
    redirect($waitlisturl, get_string('wrongtokenuser', 'enrol_bycategory'), null, notification::NOTIFY_WARNING);
}

if ($waitlist->is_on_waitlist($userid) === false) {
    redirect($waitlisturl, get_string('usernotonwaitlist', 'enrol_bycategory'), null, notification::NOTIFY_INFO);
}

// Check if token is valid.
$oneday = 86400; // ... 24 * 60 * 60
$now = time();
if ($tokenrecord->timecreated + $oneday < $now) {
    redirect($waitlisturl, get_string('tokeninvalid', 'enrol_bycategory'), null, notification::NOTIFY_INFO);
}

enrol_bycategory_delete_expired_tokens($now);

$instance = $DB->get_record('enrol', ['id' => $instanceid, 'enrol' => 'bycategory'], '*', MUST_EXIST);
$course = get_course($instance->courseid);
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$context = context_course::instance($course->id, MUST_EXIST);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

$PAGE->set_url('/enrol/bycategory/selfenrolwaitlistuser.php', ['token' => $token]);

$canenrol = $waitlist->can_enrol($instance, $userid, true);
// Sorry you missed your chance, try again next time.
if ($canenrol !== true) {
    $waitlist->reset_notification_counter($user->id);
    $DB->delete_records($tokentablename, ['id' => $tokenrecord->id]);
    redirect($waitlisturl, get_string('enrolchancemissed', 'enrol_bycategory'), null, notification::NOTIFY_INFO);
}

$enrolmethod = 'bycategory';

$enrol = enrol_get_plugin($enrolmethod);
if ($enrol === null) {
    redirect($waitlisturl, get_string('enrolmentmissing', 'enrol_bycategory'), null, notification::NOTIFY_ERROR);
}

$enrolinstances = enrol_get_instances($course->id, true);
$bycategoryinstance = null;
foreach ($enrolinstances as $enrolinstance) {
    if ($enrolinstance->enrol === $enrolmethod && $enrolinstance->id === $instance->id) {
        $bycategoryinstance = $enrolinstance;
        break;
    }
}

if ($bycategoryinstance === null) {
    redirect($waitlisturl, get_string('enrolmentmissing', 'enrol_bycategory'), null, notification::NOTIFY_ERROR);
}

$enrolresult = $enrol->enrol_user_manually($bycategoryinstance, $user->id);
if ($enrolresult === true) {
    $waitlist->remove_user($user->id);
    $DB->delete_records($tokentablename, ['id' => $tokenrecord->id]);
}

redirect($courseurl, get_string('youenrolledincourse', 'enrol'), null, notification::NOTIFY_SUCCESS);
