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
 * waiting list landing page.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

$enrolid = required_param('enrolid', PARAM_INT);

$instance = $DB->get_record('enrol', array('id' => $enrolid, 'enrol' => 'bycategory'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);
$hasmanagecapability = has_capability('enrol/bycategory:manage', $context);

$PAGE->set_url('/enrol/bycategory/waitlist.php', ['enrolid' => $enrolid]);
$PAGE->set_course($course);
$PAGE->set_title($course->shortname . ' - ' . get_string('waitlist', 'enrol_bycategory'));
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('users'));
$PAGE->navbar->add(get_string('enrolmentinstances', 'enrol'), new moodle_url('/enrol/instances.php', ['id' => $course->id]));
$PAGE->navbar->add(get_string('waitlist', 'enrol_bycategory'));

require_login();

$waitlist = new enrol_bycategory_waitlist($enrolid);

if(false === $hasmanagecapability) {
  show_user_view($waitlist, $course, $instance);
} else {
  show_management_view($waitlist, $course, $instance);
}

function show_management_view($waitlist, $course, $instance) {
  global $OUTPUT;

  $download = optional_param('download', '', PARAM_ALPHA);
  $table = new enrol_bycategory_waitlist_table($course, ['instanceid' => $instance->id]);
  $table->is_downloading(
    $download,
    get_string('waitlist_users', 'enrol_bycategory'),
    get_string('waitlist_users', 'enrol_bycategory')
  );

  if(!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('waitlist', 'enrol_bycategory'));
  }

  $url = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $instance->id]);
  $table->define_baseurl($url);
  $table->out(25, true);

  if(!$table->is_downloading()) {
    echo $OUTPUT->footer();
  }
}

function show_user_view($waitlist, $course, $instance) {
  global $USER, $OUTPUT;

  $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

  if(!$waitlist->is_on_waitlist($USER->id)) {
    redirect($courseurl);
  } else {
    $leavewaitlistexists = optional_param('leavewaitlist', null, PARAM_TEXT);
    if($leavewaitlistexists !== null) {
      $waitlist->remove_user($USER->id);
      redirect($courseurl);
    }
  }

  $form = new enrol_bycategory_leave_waitlist_form($instance);
  $waitlistposition = $waitlist->get_user_position($USER->id);
  $waitlistinfo = '';
  if($waitlistposition !== -1) {
    $waitlistinfo = get_string(
      'waitlist_position_message',
      'enrol_bycategory',
      ['waitlistposition' => $waitlistposition]
    );
  } else {
    $waitlistinfo = get_string(
      'waitlist_blocked_message',
      'enrol_bycategory',
    );
  }

  $templatecontext = [
    'waitlistinfo' => text_to_html($waitlistinfo, false, false, true),
    'form' => $form->render(),
  ];



  echo $OUTPUT->header();
  echo $OUTPUT->heading(get_string('waitlist', 'enrol_bycategory'));
  echo $OUTPUT->render_from_template('enrol_bycategory/waitlist', $templatecontext);
  echo $OUTPUT->footer();
}
