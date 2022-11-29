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
 * bycategory enrol plugin implementation.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Phpml\Helper\Optimizer\MP;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_bycategory_leave_waitlist_form extends moodleform {
  protected $instance;

  public function __construct($customdata = null) {
    parent::__construct('', $customdata);
  }

  public function definition() {
    global $USER;

    $mform = $this->_form;
    $instance = $this->_customdata;
    $this->instance = $instance;

    $mform->addElement('hidden', 'enrolid');
    $mform->setType('enrolid', PARAM_INT);
    $mform->setDefault('enrolid', $instance->id);

    $mform->addElement('hidden', 'user');
    $mform->setType('user', PARAM_INT);
    $mform->setDefault('user', $USER->id);

    //$this->add_action_buttons(false, get_string('leavewaitlist', 'enrol_bycategory'));
    $mform->addElement('submit', 'leavewaitlist', get_string('leavewaitlist', 'enrol_bycategory'));
    $mform->closeHeaderBefore('leavewaitlist');
  }
}
