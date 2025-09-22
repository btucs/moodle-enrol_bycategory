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
 * The enrol plugin bycategory is defined here.
 *
 * @package     enrol_bycategory
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

use core\message\message;
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot . '/enrol/bycategory/locallib.php');

/**
 * Extend fontawesome mapping list for custom key
 * @return array Map of key to fontawesome classes
 */
function enrol_bycategory_get_fontawesome_icon_map() {
    $iconmapping = [
        'enrol_bycategory:t/waitlist' => 'fa-list',
        'enrol_bycategory:t/remove' => 'fa-trash',
        'enrol_bycategory:t/enrol' => 'fa-user-plus',
    ];

    return $iconmapping;
}

/**
 * Class enrol_bycategory_plugin.
 */
class enrol_bycategory_plugin extends enrol_plugin {

    /** @var mixed|stdClass  */
    protected $lastenroller = null;
    /** @var int */
    protected $lasternollerinstanceid = 0;

    /**
     * Use the standard interface for adding/editing the form.
     *
     * @since Moodle 3.1.
     * @return bool
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Adds form elements to add/edit instance form.
     * @author  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
     *          based on work by 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @since Moodle 3.1.
     * @param object $instance Enrol instance or null if does not exist yet.
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return void
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $CFG, $DB;

        // Merge these two settings to one value for the single selection element.
        if ($instance->notifyall && $instance->expirynotify) {
            $instance->expirynotify = 2;
        }
        unset($instance->notifyall);

        $nameattribs = ['size' => '20', 'maxlength' => '255'];
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'server');

        $categories = $this->get_categories();
        $mform->addElement('select', 'customint1', get_string('category', 'enrol_bycategory'), $categories);
        $mform->addHelpButton('customint1', 'category', 'enrol_bycategory');

        $passattribs = ['size' => '20', 'maxlength' => '50'];
        $mform->addElement('passwordunmask', 'password', get_string('password', 'enrol_bycategory'), $passattribs);
        $mform->addHelpButton('password', 'password', 'enrol_bycategory');
        if (empty($instance->id) && $this->get_config('requirepassword')) {
            $mform->addRule('password', get_string('required'), 'required', null, 'client');
        }
        $mform->addRule('password', get_string('maximumchars', '', 50), 'maxlength', 50, 'server');

        $options = $this->get_usegroupkey_options();
        $mform->addElement('select', 'customdec1', get_string('groupkey', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customdec1', 'groupkey', 'enrol_bycategory');

        $options = ['optional' => true, 'defaultunit' => DAYSECS, 'units' => [DAYSECS, WEEKSECS]];
        $mform->addElement('duration', 'customint5', get_string('completionperiod', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customint5', 'completionperiod', 'enrol_bycategory');

        $options = $this->get_period_start_options();
        $mform->addElement('select', 'customchar1', get_string('enrolperiodcountfrom', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customchar1', 'enrolperiodcountfrom', 'enrol_bycategory');

        $options = $this->get_group_options($instance);
        $mform->addElement('select', 'customint7', get_string('autogroup', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customint7', 'autogroup', 'enrol_bycategory');

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_bycategory'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_bycategory');

        $options = $this->get_newenrols_options();
        $mform->addElement('select', 'customint6', get_string('newenrols', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customint6', 'newenrols', 'enrol_bycategory');
        $mform->disabledIf('customint6', 'status', 'eq', ENROL_INSTANCE_DISABLED);

        $roles = $this->extend_assignable_roles($context, $instance->roleid);
        $mform->addElement('select', 'roleid', get_string('role', 'enrol_bycategory'), $roles);

        $options = ['optional' => true, 'defaultunit' => 86400];
        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_bycategory'), $options);
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_bycategory');

        $options = $this->get_expirynotify_options();
        $mform->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $mform->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');

        $options = ['optional' => false, 'defaultunit' => 86400];
        $mform->addElement('duration', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), $options);
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);

        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_bycategory'), $options);
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_bycategory');

        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_bycategory'), $options);
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_bycategory');

        $options = $this->get_longtimenosee_options();
        $mform->addElement('select', 'customint2', get_string('longtimenosee', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customint2', 'longtimenosee', 'enrol_bycategory');

        $mform->addElement('text', 'customint3', get_string('maxenrolled', 'enrol_bycategory'));
        $mform->addHelpButton('customint3', 'maxenrolled', 'enrol_bycategory');
        $mform->setType('customint3', PARAM_INT);

        $cohorts = [0 => get_string('no')];
        $allcohorts = cohort_get_available_cohorts($context, 0, 0, 0);
        if (!empty($instance->customint8) && !isset($allcohorts[$instance->customint8])) {
            $c = $DB->get_record('cohort',
                                ['id' => (int) $instance->customint8],
                                'id, name, idnumber, contextid, visible',
                                IGNORE_MISSING);
            if ($c) {
                // Current cohort was not found because current user can not see it. Still keep it.
                $allcohorts[$instance->customint8] = $c;
            }
        }
        foreach ($allcohorts as $c) {
            $cohorts[$c->id] = format_string($c->name, true, ['context' => context::instance_by_id($c->contextid)]);
            if ($c->idnumber) {
                $cohorts[$c->id] .= ' ['.s($c->idnumber).']';
            }
        }
        if (!empty($instance->customint8) && !isset($allcohorts[$instance->customint8])) {
            // Somebody deleted a cohort, better keep the wrong value so that random ppl can not enrol.
            $cohorts[$instance->customint8] = get_string('unknowncohort', 'cohort', $instance->customint8);
        }
        if (count($cohorts) > 1) {
            $mform->addElement('select', 'customint8', get_string('cohortonly', 'enrol_bycategory'), $cohorts);
            $mform->addHelpButton('customint8', 'cohortonly', 'enrol_bycategory');
        } else {
            $mform->addElement('hidden', 'customint8');
            $mform->setType('customint8', PARAM_INT);
            $mform->setConstant('customint8', 0);
        }

        $options = $this->get_enablewaitlist_options();
        $mform->addElement('select', 'customchar2', get_string('enablewaitlist', 'enrol_bycategory'), $options);
        $mform->setDefault('customchar2', 0);
        $mform->addHelpButton('customchar2', 'enablewaitlist', 'enrol_bycategory');

        $mform->addElement('select', 'customint4', get_string('sendcoursewelcomemessage', 'enrol_bycategory'),
                enrol_send_welcome_email_options());
        $mform->addHelpButton('customint4', 'sendcoursewelcomemessage', 'enrol_bycategory');

        $options = ['cols' => '60', 'rows' => '8'];
        $mform->addElement('textarea', 'customtext1', get_string('customwelcomemessage', 'enrol_bycategory'), $options);
        $mform->addHelpButton('customtext1', 'customwelcomemessage', 'enrol_bycategory');

        if (enrol_accessing_via_instance($instance)) {
            $warntext = get_string('instanceeditselfwarningtext', 'core_enrol');
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), $warntext);
        }
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @since Moodle 3.1.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param array $data Array of ("fieldname" => value) of submitted data.
     * @param array $files Array of uploaded files "element_name" => tmp_file_path.
     * @param object $instance The instance data loaded from the DB.
     * @param context $context The context of the instance we are editing.
     * @return array Array of "element_name" => "error_description" if there are errors, empty otherwise.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];

        $checkpassword = false;

        if ($instance->id) {
            // Check the password if we are enabling the plugin again.
            if (($instance->status == ENROL_INSTANCE_DISABLED) && ($data['status'] == ENROL_INSTANCE_ENABLED)) {
                $checkpassword = true;
            }

            // Check the password if the instance is enabled and the password has changed.
            if (($data['status'] == ENROL_INSTANCE_ENABLED) && ($instance->password !== $data['password'])) {
                $checkpassword = true;
            }

            // Check the password if we are enabling group enrolment keys.
            if (!intval($instance->customdec1, 10) && $data['customdec1']) {
                $checkpassword = true;
            }
        } else {
            $checkpassword = true;
        }

        if ($checkpassword) {
            $require = $this->get_config('requirepassword');
            $policy = $this->get_config('usepasswordpolicy');
            if ($require && trim($data['password']) === '') {
                $errors['password'] = get_string('required');
            } else if (!empty($data['password'])) {
                if ($policy) {
                    $errmsg = '';
                    if (!check_password_policy($data['password'], $errmsg)) {
                        $errors['password'] = $errmsg;
                    }
                }
                if (
                    $data['customdec1']
                    && enrol_bycategory_check_group_enrolment_key($instance->courseid, $data['password']) !== false
                ) {
                    $errors['password'] = get_string('passwordmatchesgroupkey', 'enrol_bycategory');
                }
            }
        }

        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) && $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_bycategory');
            }
        }

        if ($data['expirynotify'] > 0 && $data['expirythreshold'] < 86400) {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }

        // Now these ones are checked by quickforms, but we may be called by the upload enrolments tool, or a webservive.
        if (core_text::strlen($data['name']) > 255) {
            $errors['name'] = get_string('err_maxlength', 'form', 255);
        }

        $validstatus = array_keys($this->get_status_options());
        $validnewenrols = array_keys($this->get_newenrols_options());
        $validperiodstarts = array_keys($this->get_period_start_options());

        $context = context_course::instance($instance->courseid);
        $validroles = array_keys($this->extend_assignable_roles($context, $instance->roleid));
        $validcategories = array_keys($this->get_categories());
        $validexpirynotify = array_keys($this->get_expirynotify_options());
        $validlongtimenosee = array_keys($this->get_longtimenosee_options());
        $validwaitlist = array_keys($this->get_enablewaitlist_options());
        $validusegroupkeyoptions = array_keys($this->get_usegroupkey_options());
        $tovalidate = [
            'enrolstartdate' => PARAM_INT,
            'enrolenddate' => PARAM_INT,
            'name' => PARAM_TEXT,
            'customint1' => $validcategories,
            'customint2' => $validlongtimenosee,
            'customint3' => PARAM_INT,
            'customint4' => PARAM_INT,
            'customint5' => PARAM_INT,
            'customint6' => $validnewenrols,
            'customint7' => PARAM_INT,
            'customchar1' => $validperiodstarts,
            'customchar2' => $validwaitlist,
            'customdec1' => $validusegroupkeyoptions,
            'customint8' => PARAM_INT,
            'status' => $validstatus,
            'enrolperiod' => PARAM_INT,
            'expirynotify' => $validexpirynotify,
            'roleid' => $validroles,
        ];

        if ($data['expirynotify'] != 0) {
            $tovalidate['expirythreshold'] = PARAM_INT;
        }
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }

    /**
     * Return whether or not, given the current state, it is possible to add a new instance
     * of this enrolment plugin to the course.
     * @author  2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param int $courseid
     * @return bool
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) || !has_capability('enrol/bycategory:config', $context)) {
            return false;
        }

        return true;
    }

    /**
     * Add new instance of enrol plugin.
     * @author  2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param object $course
     * @param array|null $fields instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, ?array $fields = null) {
        // In the form we are representing 2 db columns with one field.
        if (!empty($fields) && !empty($fields['expirynotify'])) {
            if ($fields['expirynotify'] == 2) {
                $fields['expirynotify'] = 1;
                $fields['notifyall'] = 1;
            } else {
                $fields['notifyall'] = 0;
            }
        }

        return parent::add_instance($course, $fields);
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @author  2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = $this->get_instance_defaults();

        return $this->add_instance($course, $fields);
    }

    /**
     * Returns defaults for new instances.
     * @author  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
     *          based on work by 2010 Petr Skoda {@link http://skodak.org} enrol_self
     *
     * @return array
     */
    public function get_instance_defaults() {
        $expirynotify = $this->get_config('expirynotify');
        if ($expirynotify == 2) {
            $expirynotify = 1;
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }

        $fields = [];
        $fields['status'] = $this->get_config('status');
        $fields['roleid'] = $this->get_config('roleid');
        $fields['enrolperiod'] = $this->get_config('enrolperiod');
        $fields['expirynotify'] = $expirynotify;
        $fields['notifyall'] = $notifyall;
        $fields['expirythreshold'] = $this->get_config('expirythreshold');
        $fields['customint1'] = 0; // ... categoryId.
        $fields['customint2'] = $this->get_config('longtimenosee');
        $fields['customint3'] = $this->get_config('maxenrolled');
        $fields['customint4'] = $this->get_config('sendcoursewelcomemessage');
        $fields['customint5'] = 0; // Max time since completing last course in target category.
        $fields['customint6'] = $this->get_config('newenrols');
        $fields['customint7'] = 0; // By default there is no group selected, maybe even existent.
        $fields['customint8'] = 0; // By default there is no cohorts are selected, maybe even existent.
        $fields['customchar1'] = 0; // Count completion from 0: now or 1: enrol start time.
        $fields['customchar2'] = $this->get_config('enablewaitlist'); // Enable waiting list 0: disabled, 1: enabled.
        $fields['customdec1'] = 0; // By default group enrolment keys are not used.

        return $fields;
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     * @author  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
     *          based on work by 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $OUTPUT, $USER;

        $enrolstatus = $this->can_self_enrol($instance);

        // Check if the user is on another waiting list in the same course.
        $waitlistidorfalse = $this->is_on_any_waitlist($USER->id, $instance->courseid);
        if (false !== $waitlistidorfalse) {
            $waitlisturl = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $waitlistidorfalse]);
            redirect($waitlisturl);
        }

        $waitlist = new enrol_bycategory_waitlist($instance->id);
        $waitlisturl = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $instance->id]);
        $count = $waitlist->get_count();
        // Direct enrolment is only allowed if waitlist is empty.
        if (true === $enrolstatus && $count === 0) {
            // This user can self enrol using this instance.
            $form = new enrol_bycategory_enrol_form(null, $instance);
            $instanceid = optional_param('instance', 0, PARAM_INT);
            if ($instance->id == $instanceid) {
                if ($data = $form->get_data()) {
                    $this->enrol_self($instance, $data);
                }
            }
        } else {
            if (
                $instance->customchar2 == 1 && (
                    true === $enrolstatus ||
                    $enrolstatus === get_string('maxenrolledreached', 'enrol_bycategory')
                )
            ) {
                $form = new enrol_bycategory_waitlist_form($instance);
                $instanceid = optional_param('instance', 0, PARAM_INT);
                // ... $instance->id is string
                if ($instance->id == $instanceid) {
                    if ($data = $form->get_data()) {
                        $groupid = 0;
                        if ($instance->password && intval($instance->customdec1, 10) === 1) {
                            $groupid = enrol_bycategory_check_group_enrolment_key($instance->courseid, $data->enrolpassword);
                            if ($groupid === false) {
                                $groupid = 0;
                            }
                        }

                        $waitlist->add_user($data->user, $groupid);
                        redirect($waitlisturl);
                    }
                }
            } else {
                // This user can not self enrol using this instance. Using an empty form to keep
                // the UI consistent with other enrolment plugins that returns a form.
                $data = new stdClass();
                $data->header = $this->get_instance_name($instance);
                $data->info = $enrolstatus;

                // The can_self_enrol call returns a button to the login page if the user is a
                // guest, setting the login url to the form if that is the case.
                $url = isguestuser() ? get_login_url() : null;
                $form = new enrol_bycategory_empty_form($url, $data);
            }
        }

        ob_start();
        $form->display();
        $output = ob_get_clean();

        return $OUTPUT->box($output);
    }

    /**
     * Checks if user can self enrol.
     *
     * @param stdClass $instance enrolment instance
     * @param bool $checkuserenrolment if true will check if user enrolment is inactive.
     *             used by navigation to improve performance.
     * @return bool|string true if successful, else error message or false.
     */
    public function can_self_enrol(stdClass $instance, $checkuserenrolment = true) {
        global $USER, $OUTPUT, $DB;

        if ($checkuserenrolment) {
            if (isguestuser()) {
                // Can not enrol guest.
                return get_string('noguestaccess', 'enrol') . $OUTPUT->continue_button(get_login_url());
            }
            // Get all enrol methods the current course and check if user is already enroled.
            $enrolids = $DB->get_fieldset_select('enrol', 'id', 'courseid = :courseid', ['courseid' => $instance->courseid]);
            list($insql, $inparams) = $DB->get_in_or_equal($enrolids, SQL_PARAMS_NAMED);
            $params = ['userid' => $USER->id] + $inparams;
            if ($DB->count_records_select('user_enrolments', "userid = :userid AND enrolid $insql", $params) > 0) {
                return get_string('canntenrol', 'enrol_bycategory');
            }
        }

        $waitlist = new enrol_bycategory_waitlist($instance->id);

        return $waitlist->can_enrol($instance, $USER->id);
    }

    /**
     * Return information for enrolment instance containing list of parameters required
     * for enrolment, name of enrolment plugin etc.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance enrolment instance
     * @return stdClass instance info.
     */
    public function get_enrol_info(stdClass $instance) {

        $instanceinfo = new stdClass();
        $instanceinfo->id = $instance->id;
        $instanceinfo->courseid = $instance->courseid;
        $instanceinfo->type = $this->get_name();
        $instanceinfo->name = $this->get_instance_name($instance);
        $instanceinfo->status = $this->can_self_enrol($instance);

        return $instanceinfo;
    }

    /**
     * Returns localised name of enrol instance
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance->name)) {
            if (!empty($instance->roleid) && $role = $DB->get_record('role', ['id' => $instance->roleid])) {
                $role = ' (' . role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING)) . ')';
            } else {
                $role = '';
            }
            $enrol = $this->get_name();

            return get_string('pluginname', 'enrol_'.$enrol) . $role;
        } else {
            return format_string($instance->name);
        }
    }

    /**
     * Update instance of enrol plugin.
     * @author  2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        // In the form we are representing 2 db columns with one field.
        if ($data->expirynotify == 2) {
            $data->expirynotify = 1;
            $data->notifyall = 1;
        } else {
            $data->notifyall = 0;
        }
        // Keep previous/default value of disabled expirythreshold option.
        if (!$data->expirynotify) {
            $data->expirythreshold = $instance->expirythreshold;
        }
        // Add previous value of newenrols if disabled.
        if (!isset($data->customint6)) {
            $data->customint6 = $instance->customint6;
        }

        return parent::update_instance($instance, $data);
    }

    /**
     * enrol current user to course
     * @author  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
     *          2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance enrolment instance
     * @param stdClass $data data needed for enrolment.
     * @return bool|array true if enroled else error code and messege
     */
    public function enrol_self(stdClass $instance, $data = null) {
        global $DB, $USER, $CFG;

        // Don't enrol user if password is not passed when required.
        if ($instance->password && !isset($data->enrolpassword)) {
            return;
        }

        $enrolresult = $this->enrol_user_manually($instance, $USER->id);

        \core\notification::success(get_string('youenrolledincourse', 'enrol'));

        // Send welcome message.
        if ($instance->customint4 != ENROL_DO_NOT_SEND_EMAIL) {
            $this->email_welcome_message($instance, $USER);
        }

        return $enrolresult;
    }

    /**
     * enrol a user to course
     * @param stdClass $instance enrolment instance
     * @param int $userid
     * @param int $groupid optional group id to add user to, in case of enroling from waiting list
     * @return bool|array true if enroled else error code and message
     */
    public function enrol_user_manually(stdClass $instance, $userid, $groupid = 0) {
        $timestart = time();
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }

        $this->enrol_user($instance, $userid, $instance->roleid, $timestart, $timeend);

        // If the user is enroled from the waiting list, this bypasses the enrolment key check and searching the groupid.
        if ($groupid > 0) {
            // Add user to group.
            groups_add_member($groupid, $userid, 'enrol_bycategory', $instance->id);
            return true;
        }

        // If the user enroled via group enrolment key, this bypasses the automatically adding to a
        // ... specific group based on the enrolment method settings.
        if ($instance->password && $instance->customdec1) {
            $groupid = enrol_bycategory_check_group_enrolment_key($instance->courseid, $data->enrolpassword);
            if ($groupid !== false) {
                // Add user to group.
                groups_add_member($groupid, $userid, 'enrol_bycategory', $instance->id);
                return true;
            }
        }

        // Automatically add user to group based on enrolment method settings.
        if ($instance->customint7 > 0) {
            groups_add_member($instance->customint7, $userid, 'enrol_bycategory', $instance->id);
        }

        return true;
    }

    /**
     * Gets a list of roles that this user can assign for the course as the default for by category-enrolment.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param context $context the context.
     * @param integer $defaultrole the id of the role that is set as the default for by category-enrolment
     * @return array index is the role id, value is the role name
     */
    public function extend_assignable_roles($context, $defaultrole) {
        global $DB;

        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', ['id' => $defaultrole])) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }

        return $roles;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);

        if (!has_capability('enrol/bycategory:config', $context)) {
            return false;
        }

        return true;
    }

    /**
     * Restore instance and map settings.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;
        if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
            $merge = false;
        } else {
            $merge = [
                'courseid' => $data->courseid,
                'enrol' => $this->get_name(),
                'status' => $data->status,
                'roleid' => $data->roleid,
            ];
        }
        if ($merge && $instances = $DB->get_records('enrol', $merge, 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {

            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Restore user enrolment.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $userid
     * @param int $oldinstancestatus
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    /**
     * Restore role assignment.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance
     * @param int $roleid
     * @param int $userid
     * @param int $contextid
     */
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
        // This is necessary only because we may migrate other types to this instance,
        // we do not use component in manual or self enrol.
        role_assign($roleid, $userid, $contextid, '', 0);
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/bycategory:config', $context);
    }

    /**
     * Does this plugin assign protected roles are can they be manually removed?
     * @return bool - false means anybody may tweak roles, it does not use itemid and component when assigning roles
     */
    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }

    /**
     * Does this plugin allow manual unenrolment of all users?
     * All plugins allowing this must implement 'enrol/xxx:unenrol' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol others freely,
     *              - false means nobody may touch user_enrolments
     */
    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users manually.
        return true;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * All plugins allowing this must implement 'enrol/xxx:manage' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means it is possible to change enrol period and status in user_enrolments table
     */
    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status.
        return true;
    }

    /**
     * Does this plugin support some way to user to self enrol?
     *
     * @param stdClass $instance course enrol instance
     *
     * @return bool - true means show "Enrol me in this course" link in course UI
     */
    public function show_enrolme_link(stdClass $instance) {

        if (true !== $this->can_self_enrol($instance, false)) {
            return false;
        }

        return true;
    }

    /**
     * Sync all meta course links.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param progress_trace $trace
     * @param int $courseid one course, empty mean all
     * @return int 0 means ok, 1 means error, 2 means plugin disabled
     */
    public function sync(progress_trace $trace, $courseid = null) {
        global $DB;

        if (!enrol_is_enabled('bycategory')) {
            $trace->finished();
            return 2;
        }

        // Unfortunately this may take a long time, execution can be interrupted safely here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Verifying bycategory-enrolments...');

        $params = [
            'now' => time(),
            'useractive' => ENROL_USER_ACTIVE,
            'courselevel' => CONTEXT_COURSE,
        ];

        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        /* Note: the logic of self enrolment guarantees that user logged in at least once (=== u.lastaccess set)
                 and that user accessed course at least once too (=== user_lastaccess record exists).

            First deal with users that did not log in for a really long time - they do not have user_lastaccess records.
        */
        $sql = "SELECT e.*, ue.userid
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid
                    AND e.enrol = 'bycategory'
                    AND e.customint2 > 0)
                  JOIN {user} u ON u.id = ue.userid
                 WHERE :now - u.lastaccess > e.customint2
                       $coursesql";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $instance) {
            $userid = $instance->userid;
            unset($instance->userid);
            $this->unenrol_user($instance, $userid);
            $days = $instance->customint2 / DAYSECS;
            $trace->output("unenrolling user $userid from course $instance->courseid " .
                "as they did not log in for at least $days days", 1);
        }
        $rs->close();

        // Now unenrol from course user did not visit for a long time.
        $sql = "SELECT e.*, ue.userid
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'bycategory' AND e.customint2 > 0)
                  JOIN {user_lastaccess} ul ON (ul.userid = ue.userid AND ul.courseid = e.courseid)
                 WHERE :now - ul.timeaccess > e.customint2
                       $coursesql";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $instance) {
            $userid = $instance->userid;
            unset($instance->userid);
            $this->unenrol_user($instance, $userid);
            $days = $instance->customint2 / DAYSECS;
            $trace->output("unenrolling user $userid from course $instance->courseid " .
                "as they did not access the course for at least $days days", 1);
        }
        $rs->close();

        $trace->output('...user bycategory-enrolment updates finished.');
        $trace->finished();

        $this->process_expirations($trace, $courseid);

        return 0;
    }

    /**
     * Inform users of the waitlist about vacancies
     *
     * @param progress_trace $trace
     */
    public function send_waitlist_notifications($trace) {
        global $DB, $USER;

        $name = $this->get_name();
        if (!enrol_is_enabled(($name))) {
            $trace->finished();
            return;
        }

        // Unfortunately this may take a long time, it should not be interrupted,
        // otherwise users get duplicate notification.

        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Processing '.$name.' waitlist notifications...');

        $courseswithspace = enrol_bycategory_waitlist::select_courses_with_available_space();
        if (count($courseswithspace) === 0) {
            $trace->output('...notification processing finished.');
            $trace->finished();

            return;
        }

        $waitlistentries = enrol_bycategory_waitlist::select_users_from_waitlist_for_notification(array_keys($courseswithspace));

        $count = count($waitlistentries);
        $trace->output('preparing to notifify '.$count.' users.' );

        $userfrom = core_user::get_noreply_user();
        $now = time();

        if ($count > 0) {
            foreach ($waitlistentries as $waitlistentry) {

                $token = $this->create_token();
                $DB->insert_record('enrol_bycategory_token', [
                    'token' => $token,
                    'waitlistid' => $waitlistentry->id,
                    'userid' => $waitlistentry->userid,
                    'usermodified' => $USER->id,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ], false, false);

                $course = $courseswithspace[$waitlistentry->instanceid];
                $user = $DB->get_record('user', ['id' => $waitlistentry->userid]);
                $oldforcelang = force_current_language($user->lang);

                $usernotifycount = get_config('enrol_bycategory', 'waitlistnotifycount');
                if ($usernotifycount === false) {
                    $usernotifycount = 5;
                }

                $a = new stdClass();
                $a->coursename = format_string($course->fullname, true, []);
                $a->confirmenrolurl = (string)new moodle_url('/enrol/bycategory/selfenrolwaitlistuser.php', ['token' => $token]);
                $a->leavewaitlisturl = (string)new moodle_url('/course/view.php', ['id' => $course->id]);
                $a->userfullname = fullname($user, true);
                $a->notifyamount = $usernotifycount - 1;

                $subject = get_string('waitlist_notification_subject', 'enrol_bycategory', $a);
                $body = get_string('waitlist_notification_body', 'enrol_bycategory', $a);
                $markdownbody = str_replace([
                    $a->confirmenrolurl,
                    $a->leavewaitlisturl,
                ], [
                    "[$a->confirmenrolurl]($a->confirmenrolurl)",
                    "[$a->leavewaitlisturl]($a->leavewaitlisturl)",
                ], $body);

                $message = new message();
                $message->component = 'enrol_bycategory';
                $message->name = 'waitlist_notification';
                $message->userfrom = $userfrom;
                $message->userto = $user;
                $message->subject = $subject;
                $message->fullmessage = $body;
                $message->fullmessageformat = FORMAT_PLAIN;
                $message->fullmessagehtml = markdown_to_html($markdownbody);
                $message->smallmessage = $subject;
                $message->contexturlname = $a->coursename;
                $message->contexturl = $a->confirmenrolurl;
                $message->notification = 1; // This is only set to 0 for personal messages between users.

                $messageid = message_send($message);
                if ($messageid) {
                    $trace->output("notifying user $user->id that there is a spot available in $course->id.");
                } else {
                    $trace->output("error notifying user $user->id that there is a spot available in $course->id.");
                }

                force_current_language($oldforcelang);
            }

            enrol_bycategory_waitlist::increase_notified(array_keys($waitlistentries));
        }

        $trace->output('...notification processing finished.');
        $trace->finished();
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        $waitlisticon = '';
        if ($instance->customchar2 == 1) {
            $linkparams = ['enrolid' => $instance->id];
            $waitlistlink = new moodle_url('/enrol/bycategory/waitlist.php', $linkparams);
            $badgerenderer = new enrol_bycategory_badge_action_icon();
            $waitlist = new enrol_bycategory_waitlist($instance->id);
            $waitlistusercount = $waitlist->get_count();
            $waitlisticon = $badgerenderer->badge_action_icon(
                $waitlistlink,
                new pix_icon(
                    't/waitlist',
                    get_string('waitlist_active', 'enrol_bycategory', $waitlistusercount),
                    'enrol_bycategory',
                    ['class' => 'iconsmall fa fa-fw']
                ),
                $waitlistusercount
            );
        } else {
            $waitlisticon = $OUTPUT->pix_icon(
                't/waitlist',
                get_string('waitlist_deactivated', 'enrol_bycategory'),
                'enrol_bycategory',
                ['class' => 'iconsmall fa fa-fw dimmed_text']
            );
        }

        $icons = parent::get_action_icons($instance);

        array_unshift($icons, $waitlisticon);

        return $icons;
    }

    /**
     * @deprecated since Moodle 4.4
     */
    #[\core\attribute\deprecated('enrol_plugin::get_welcome_message_contact', since: '4.4', mdl: 'MDL-4188', final: true)]
    public function get_welcome_email_contact() {
        \core\deprecation::emit_deprecation_if_present([self::class, __FUNCTION__]);
    }

    /**
     * Get the "from" contact which the message will be sent from.
     *
     * @param int $sendoption send email from constant ENROL_SEND_EMAIL_FROM_*
     * @param context $context where the user will be fetched from.
     * @return null|stdClass the contact user object.
     */
    public function get_welcome_message_contact(int $sendoption, context $context): ?stdClass {
        $contact = parent::get_welcome_message_contact($sendoption, $context);

        // The parent method only handles enrol/self:holdkey.
        if ($contact === null && $sendoption === ENROL_SEND_EMAIL_FROM_KEY_HOLDER) {
            // Send as the first user with enrol/bycategory:holdkey capability assigned in the course.
            [$sort] = users_order_by_sql('u');
            $keyholders = get_users_by_capability($context, 'enrol/bycategory:holdkey', 'u.*', $sort);
            if (!empty($keyholders)) {
                $contact = array_values($keyholders)[0];
            }
        }

        return $contact;
    }

    /**
     * Send welcome email to specified user.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @param stdClass $instance
     * @param stdClass $user user record
     * @return void
     */
    protected function email_welcome_message($instance, $user) {
        global $CFG, $DB;

        $course = get_course($instance->courseid);
        $context = context_course::instance($course->id);

        $a = new stdClass();
        $a->coursename = format_string($course->fullname, true, ['context' => $context]);
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&course=$course->id";

        if (!empty($instance->customtext1) &&trim($instance->customtext1) !== '') {
            $message = $instance->customtext1;
            $key = ['{$a->coursename}', '{$a->profileurl}', '{$a->fullname}', '{$a->email}'];
            $value = [$a->coursename, $a->profileurl, fullname($user), $user->email];
            $message = str_replace($key, $value, $message);
            if (strpos($message, '<') === false) {
                // Plain text only.
                $messagetext = $message;
                $messagehtml = text_to_html($messagetext, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $messagehtml = format_text($message, FORMAT_MOODLE, [
                    'context' => $context,
                    'para' => false,
                    'newlines' => true,
                    'filter' => true,
                ]);
                $messagetext = html_to_text($messagehtml);
            }
        } else {
            $messagetext = get_string('welcometocoursetext', 'enrol_bycategory', $a);
            $messagehtml = text_to_html($messagetext, null, false, true);
        }

        $subject = get_string('welcometocourse', 'enrol_bycategory', format_string(
            $course->fullname, true, ['context' => $context]
        ));

        $sendoption = $instance->customint4;
        $contact = $this->get_welcome_message_contact($sendoption, $context);

        // Directly emailing welcome message rather than using messaging.
        email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
    }

    /**
     * Returns the user who is responsible for self enrolments in given instance.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * Usually it is the first editing teacher - the person with "highest authority"
     * as defined by sort_by_roleassignment_authority() having 'enrol/self:manage'
     * capability.
     *
     * @param int $instanceid enrolment instance id
     * @return stdClass user record
     */
    protected function get_enroller($instanceid) {
        global $DB;

        if ($this->lasternollerinstanceid == $instanceid && $this->lastenroller) {
            return $this->lastenroller;
        }

        $instance = $DB->get_record('enrol', ['id' => $instanceid, 'enrol' => $this->get_name()], '*', MUST_EXIST);
        $context = context_course::instance($instance->courseid);

        if ($users = get_enrolled_users($context, 'enrol/bycategory:manage')) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lastenroller = reset($users);
            unset($users);
        } else {
            $this->lastenroller = parent::get_enroller($instanceid);
        }

        $this->lasternollerinstanceid = $instanceid;

        return $this->lastenroller;
    }

    /**
     * Return an array of valid options for the status.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @return array
     */
    protected function get_status_options() {
        $options = [
            ENROL_INSTANCE_ENABLED => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no'),
        ];
        return $options;
    }

    /**
     * Return an array of valid options for the newenrols property.
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @return array
     */
    protected function get_newenrols_options() {
        $options = [1 => get_string('yes'), 0 => get_string('no')];
        return $options;
    }

    /**
     * Return an array of valid options for the enablewaitlist (customchar2) property
     * @return array
     */
    protected function get_enablewaitlist_options() {
        $options = [1 => get_string('yes'), 0 => get_string('no')];
        return $options;
    }

    /**
     * Return an array of valid options for the usegroupkey (customdec1) property
     * @return array
     */
    protected function get_usegroupkey_options() {
        $options = [0 => get_string('no'), 1 => get_string('yes')];
        return $options;
    }

    /**
     * Return an array of valid options for customchar1 (counting start time for enrolment period) property
     *
     * @return array
     */
    protected function get_period_start_options() {
        $options = [
            0 => get_string('enrolperiodcountfromnow', 'enrol_bycategory'),
            1 => get_string('enrolperiodcountfromenrollstart', 'enrol_bycategory'),
        ];

        return $options;
    }

    /**
     * Return an array of valid options for the expirynotify property.
     *
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @return array
     */
    protected function get_expirynotify_options() {
        $options = [
            0 => get_string('no'),
            1 => get_string('expirynotifyenroller', 'enrol_bycategory'),
            2 => get_string('expirynotifyall', 'enrol_bycategory'),
        ];
        return $options;
    }

    /**
     * Return an array of valid options for the longtimenosee property.
     *
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     *
     * @return array
     */
    protected function get_longtimenosee_options() {
        $options = [
            0 => get_string('never'),
            1800 * 3600 * 24 => get_string('numdays', '', 1800),
            1000 * 3600 * 24 => get_string('numdays', '', 1000),
            365 * 3600 * 24 => get_string('numdays', '', 365),
            180 * 3600 * 24 => get_string('numdays', '', 180),
            150 * 3600 * 24 => get_string('numdays', '', 150),
            120 * 3600 * 24 => get_string('numdays', '', 120),
            90 * 3600 * 24 => get_string('numdays', '', 90),
            60 * 3600 * 24 => get_string('numdays', '', 60),
            30 * 3600 * 24 => get_string('numdays', '', 30),
            21 * 3600 * 24 => get_string('numdays', '', 21),
            14 * 3600 * 24 => get_string('numdays', '', 14),
            7 * 3600 * 24 => get_string('numdays', '', 7),
        ];
        return $options;
    }

    /**
     * Check if the given user is on any waitlist in this course
     * @param int $userid
     * @param int $courseid
     *
     * @return int | boolean id of the enrol method where the user is on the waiting list or false
     */
    protected function is_on_any_waitlist($userid, $courseid) {
        global $DB;

        $sql = "SELECT e.id FROM {enrol_bycategory_waitlist} ebw
            JOIN {enrol} e ON e.id = ebw.instanceid
        WHERE e.enrol = :pluginname
            AND e.status = :status
            AND e.courseid = :courseid
            AND ebw.userid = :userid";

        $params = [
            'pluginname' => 'bycategory',
            'userid' => $userid,
            'courseid' => $courseid,
            'status' => ENROL_INSTANCE_ENABLED,
        ];

        $waitlistid = $DB->get_field_sql($sql, $params);

        return $waitlistid;
    }

    /**
     * Return a random token string
     *
     * @return string
     */
    private function create_token() {
        return bin2hex(random_bytes(32));
    }

    /**
     * return an array of available categories
     *
     * @return array Map of category id and name
     */
    private function get_categories() {

        $categories = \core_course_category::make_categories_list();
        // Specifying no category makes it work like normal self enrol.
        // Categories start with index 1 so it's safe to add a 0 entry.
        // array_unshift doesn't work, because it re-indexes the array.
        $categories = [0 => get_string('nocategory', 'enrol_bycategory')] + $categories;

        return $categories;
    }

    /**
     * Return an array of available groups
     * @param object $instance Enrol instance or null if does not exist yet.
     *
     * @return array Map of group id and name
     */
    private function get_group_options($instance) {

        $groups = groups_get_all_groups($instance->courseid);
        $values = array_map(static function ($group) {
            return $group->name;
        }, array_values($groups));

        return [0 => get_string('nogroup', 'enrol_bycategory')] + array_combine(array_keys($groups), $values);
    }

    /**
     * Check if data is valid for a given enrolment plugin
     *
     * @author 2010 Petr Skoda  {@link http://skodak.org} enrol_self
     * @param array $enrolmentdata enrolment data to validate.
     * @param int|null $courseid Course ID.
     * @return array Errors
     */
    public function validate_enrol_plugin_data(array $enrolmentdata, ?int $courseid = null): array {
        global $CFG, $DB;

        $errors = [];

        $plugin = $this->get_name();
        if (!enrol_is_enabled($plugin)) {
            $pluginname = get_string('pluginname', 'enrol_' . $plugin);
            $errors['plugindisabled'] = new lang_string('plugindisabled', 'enrol', $pluginname);

        }

        $policy = $this->get_config('usepasswordpolicy');
        if (!empty($enrolmentdata['password'])) {
            if ($policy) {
                $errarray = get_password_policy_errors($enrolmentdata['password']);
                foreach ($errarray as $i => $err) {
                    $errors['enrol_bycategory' . $i] = $err;
                }
            }

            if ($courseid) {
                // This is bad - no way to identify which instance it is.
                // So if any instance in course uses group key we should error.
                $usegroupenrolmentkeys =
                    $DB->count_records('enrol', ['courseid' => $courseid, 'enrol' => 'bycategory', 'customdec1' => 1]);
                if (
                    $usegroupenrolmentkeys
                    && enrol_bycategory_check_group_enrolment_key($courseid, $enrolmentdata['password']) !== false
                ) {
                    $errors['errorpasswordmatchesgroupkey'] =
                        new lang_string('passwordmatchesgroupkey', 'enrol_bycategory', $enrolmentdata['password']);
                }
            }
        }
        return $errors;
    }
}
