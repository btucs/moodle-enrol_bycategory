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
 * Remove a user from the waiting list.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

$enrolid = required_param('enrolid', PARAM_INT);
$userid = required_param('uid', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

$instance = $DB->get_record('enrol', ['id' => $enrolid, 'enrol' => 'bycategory'], '*', MUST_EXIST);
$course = get_course($instance->courseid);
$context = context_course::instance($course->id, MUST_EXIST);

$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

$PAGE->set_url('/enrol/bycategory/removewaitlistuser.php', ['enrolid' => $enrolid, 'uid' => $user->id]);
require_login($course);
require_capability('enrol/bycategory:manage', $context);

$waitlist = new enrol_bycategory_waitlist($instance->id);
$waitlisturl = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $enrolid]);
if ($confirm && confirm_sesskey()) {
    if ($waitlist->is_on_waitlist($user->id)) {
        $waitlist->remove_user($user->id);
    }

    redirect($waitlisturl);
}

$yesurl = new moodle_url($PAGE->url, ['confirm' => 1, 'sesskey' => sesskey()]);
$message = get_string('removewaitlistuserconfirm', 'enrol_bycategory', [
    'user' => fullname($user, true),
    'course' => format_string($course->fullname),
]);

$fullname = fullname($user);
$title = get_string('removewaitlistuser', 'enrol_bycategory');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('users'));
$PAGE->navbar->add(get_string('enrolmentinstances', 'enrol'), new moodle_url('/enrol/instances.php', ['id' => $course->id]));
$PAGE->navbar->add(get_string('waitlist', 'enrol_bycategory'), $waitlisturl);
$PAGE->navbar->add($title);
$PAGE->navbar->add($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($fullname);
echo $OUTPUT->confirm($message, $yesurl, $waitlisturl);
echo $OUTPUT->footer();
