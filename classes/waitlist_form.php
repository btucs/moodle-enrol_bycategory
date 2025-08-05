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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Moodle Form to enrol into the waiting list
 */
class enrol_bycategory_waitlist_form extends moodleform {
    /** @var stdClass */
    protected $instance;

    /**
     * Constructor
     * @param mixed $customdata
     */
    public function __construct($customdata = null) {
        parent::__construct('', $customdata);
    }

    /**
     * Return form identifier
     * @return string;
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id . '_' . get_class($this);
        return $formid;
    }

    /**
     * Configure MoodleQuickForm instance
     */
    public function definition() {
        global $USER;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('bycategory');

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'selfheader', $heading);

        $maxenrolledmessage = get_string('maxenrolledreached', 'enrol_bycategory');
        $joinwaitlistmessage = get_string('joinwaitlistmessage', 'enrol_bycategory');
        $message = <<<EOD
    <p>$maxenrolledmessage</p>
    <p>$joinwaitlistmessage</p>
EOD;
        $mform->addElement('html', $message);

        if($instance->password) {
            $mform->addElement('password', 'enrolpassword', get_string('password', 'enrol_bycategory'),
                    ['id' => 'enrolpassword_'.$instance->id]);
        }

        $this->add_action_buttons(false, get_string('joinwaitlist', 'enrol_bycategory'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);

        $mform->addElement('hidden', 'user');
        $mform->setType('user', PARAM_INT);
        $mform->setDefault('user', $USER->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($instance->password) {
            if ($data['enrolpassword'] !== $instance->password) {
                if (intval($instance->customdec1, 10) > 0) {
                    // Check group enrolment key.
                    if (!enrol_bycategory_check_group_enrolment_key($instance->courseid, $data['enrolpassword'])) {
                        // We can not hint because there are probably multiple passwords.
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_bycategory');
                    }

                } else {
                    $plugin = enrol_get_plugin('bycategory');
                    if ($plugin->get_config('showhint')) {
                        $hint = core_text::substr($instance->password, 0, 1);
                        $errors['enrolpassword'] = get_string('passwordinvalidhint', 'enrol_bycategory', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_bycategory');
                    }
                }
            }
        }

        return $errors;
    }
}
