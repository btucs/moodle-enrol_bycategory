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
 * Plugin administration pages are defined here.
 *
 * @package     enrol_bycategory
 * @category    admin
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 *              2010 Petr Skoda  {@link http://skodak.org}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // General Settings.
    $settings->add(new admin_setting_heading('enrol_bycategory_settings', '', get_string('pluginname_desc', 'enrol_bycategory')));

    /* Note: let's reuse the ext sync constants and strings here, internally it is very similar,
             it describes what should happend when users are not supposed to be enerolled any more.
    */
    $options = [
        ENROL_EXT_REMOVED_KEEP => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL => get_string('extremovedunenrol', 'enrol'),
    ];
    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/expiredaction',
            get_string('expiredaction', 'enrol_bycategory'),
            get_string('expiredaction_help', 'enrol_bycategory'),
            ENROL_EXT_REMOVED_KEEP,
            $options
        )
    );

    $options = [];
    for ($i = 0; $i < 24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(
        new admin_setting_configselect('enrol_bycategory/expirynotifyhour',
            get_string('expirynotifyhour', 'core_enrol'),
            '',
            6,
            $options
        )
    );

    // Enrol instance defaults.
    $settings->add(
        new admin_setting_heading(
            'enrol_bycategory_defaults',
            get_string('enrolinstancedefaults', 'admin'),
            get_string('enrolinstancedefaults_desc', 'admin')
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_bycategory/defaultenrol',
            get_string('defaultenrol', 'enrol'),
            get_string('defaultenrol_desc', 'enrol'),
            1
        )
    );

    $options = [
        ENROL_INSTANCE_ENABLED => get_string('yes'),
        ENROL_INSTANCE_DISABLED => get_string('no'),
    ];

    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/status',
            get_string('status', 'enrol_bycategory'),
            get_string('status_desc', 'enrol_bycategory'),
            ENROL_INSTANCE_DISABLED,
            $options
        )
    );

    $options = [1 => get_string('yes'), 0 => get_string('no')];
    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/newenrols',
            get_string('newenrols', 'enrol_bycategory'),
            get_string('newenrols_desc', 'enrol_bycategory'),
            1,
            $options
        )
    );

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(
            new admin_setting_configselect(
                'enrol_bycategory/roleid',
                get_string('defaultrole', 'enrol_bycategory'),
                get_string('defaultrole_desc', 'enrol_bycategory'),
                $student->id ?? null,
                $options
            )
        );
    }

    $settings->add(
        new admin_setting_configduration(
            'enrol_bycategory/enrolperiod',
            get_string('enrolperiod', 'enrol_bycategory'),
            get_string('enrolperiod_desc', 'enrol_bycategory'),
            0
        )
    );

    $options = [
        0 => get_string('enrolperiodcountfromnow', 'enrol_bycategory'),
        1 => get_string('enrolperiodcountfromenrollstart', 'enrol_bycategory'),
    ];

    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/enrolperiodcountfrom',
            get_string('enrolperiodcountfrom', 'enrol_bycategory'),
            get_string('enrolperiodcountfrom_help', 'enrol_bycategory'),
            0,
            $options
        )
    );

    $options = [
        0 => get_string('no'),
        1 => get_string('expirynotifyenroller', 'enrol_bycategory'),
        2 => get_string('expirynotifyall', 'enrol_bycategory'),
    ];

    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/expirynotify',
            get_string('expirynotify', 'core_enrol'),
            get_string('expirynotify_help', 'core_enrol'),
            0,
            $options
        )
    );

    $settings->add(
        new admin_setting_configduration(
            'enrol_bycategory/expirythreshold',
            get_string('expirythreshold', 'core_enrol'),
            get_string('expirythreshold_help', 'core_enrol'),
            86400,
            86400
        )
    );

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

    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/longtimenosee',
            get_string('longtimenosee', 'enrol_bycategory'),
            get_string('longtimenosee_help', 'enrol_bycategory'),
            0,
            $options
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_bycategory/maxenrolled',
            get_string('maxenrolled', 'enrol_bycategory'),
            get_string('maxenrolled_help', 'enrol_bycategory'),
            0,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/sendcoursewelcomemessage',
            get_string('sendcoursewelcomemessage', 'enrol_bycategory'),
            get_string('sendcoursewelcomemessage_help', 'enrol_bycategory'),
            ENROL_SEND_EMAIL_FROM_COURSE_CONTACT,
            enrol_send_welcome_email_options()
        )
    );

    $options = [0 => get_string('no'), 1 => get_string('yes')];
    $settings->add(
        new admin_setting_configselect(
            'enrol_bycategory/enablewaitlist',
            get_string('enablewaitlist', 'enrol_bycategory'),
            get_string('enablewaitlist_help', 'enrol_bycategory'),
            0,
            $options
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_bycategory/waitlistnotifycount',
            get_string('waitlistnotifycount', 'enrol_bycategory'),
            get_string('waitlistnotifycount_help', 'enrol_bycategory'),
            5,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_bycategory/waitlistnotifylimit',
            get_string('waitlistnotifylimit', 'enrol_bycategory'),
            get_string('waitlistnotifylimit_help', 'enrol_bycategory'),
            5,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_bycategory/waitlistnotifyperiod',
            get_string('waitlistnotifyperiod', 'enrol_bycategory'),
            get_string('waitlistnotifyperiod_help', 'enrol_bycategory'),
            3,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'enrol_bycategory/externalseniorityapi',
            get_string('externalseniorityapi', 'enrol_bycategory'),
            get_string('externalseniorityapi_help', 'enrol_bycategory'),
            null, PARAM_RAW, 100)
    );

}
