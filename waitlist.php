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
require_once($CFG->dirroot.'/enrol/bycategory/locallib.php');
defined('MOODLE_INTERNAL') || die();

$enrolid = required_param('enrolid', PARAM_INT);

$instance = $DB->get_record('enrol', ['id' => $enrolid, 'enrol' => 'bycategory'], '*', MUST_EXIST);
$course = get_course($instance->courseid);
$context = context_course::instance($course->id, MUST_EXIST);
$hasmanagecapability = has_capability('enrol/bycategory:manage', $context);

$PAGE->set_url('/enrol/bycategory/waitlist.php', ['enrolid' => $enrolid]);
$PAGE->set_course($course);
$PAGE->set_title($course->shortname . ' - ' . get_string('waitlist', 'enrol_bycategory'));
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('users'));
$PAGE->navbar->add(get_string('enrolmentinstances', 'enrol'), new moodle_url('/enrol/instances.php', ['id' => $course->id]));
$name = $instance->name ?: get_string('waitlist', 'enrol_bycategory');
$PAGE->navbar->add($name);

require_login();

$waitlist = new enrol_bycategory_waitlist($enrolid);

if (false === $hasmanagecapability) {
    enrol_bycategory_waitlist_show_user_view($waitlist, $course, $instance);
} else {
    enrol_bycategory_waitlist_show_management_view($waitlist, $course, $instance);
}
