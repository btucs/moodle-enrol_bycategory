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
 * Plugin strings are defined here.
 *
 * @package     enrol_bycategory
 * @category    string
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['canntenrol'] = 'Enrolment is disabled or inactive';
$string['canntenrolearly'] = 'You cannot enrol yet; enrolment starts on {$a}.';
$string['canntenrollate'] = 'You cannot enrol any more, since enrolment ended on {$a}.';
$string['category'] = 'Course completed in Category';
$string['category_help'] = 'Select the category in which a course has to be completed to be eligible to access this course.

If you select "no category limitation" anyone can enrol.';
$string['confirmbulkdeleteenrolment'] = 'Are you sure you want to delete these user enrolments?';
$string['customwelcomemessage'] = 'Custom welcome message';
$string['customwelcomemessage_help'] = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}';
$string['completionperiod'] = 'Timelimit since completion';
$string['completionperiod_help'] = 'Allowed duration since completing a course in the configured category.';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during self enrolment';
$string['deleteselectedusers'] = 'Delete selected user enrolments';
$string['editselectedusers'] = 'Edit selected user enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can enrol themselves until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolme'] = 'Enrol me';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user enrols themselves. If disabled, the enrolment duration will be unlimited.';
$string['enrolperiodcountfrom'] = 'Start counting duration from';
$string['enrolperiodcountfrom_help'] = 'The specified enrolment duration can either start counting from now backwards by the given amount of time or from the time the enrolment of the course was opened. In any case the day is the deciding factor not the time.';
$string['enrolperiodcountfromnow'] = 'current time';
$string['enrolperiodcountfromenrollstart'] = 'enrol start date';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can enrol themselves from this date onward only.';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'By Category enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'By Category enrolment in the course \'{$a->course}\' will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['expirynotifyall'] = 'Teacher and enrolled user';
$string['expirynotifyenroller'] = 'Teacher only';
$string['longtimenosee'] = 'Unenrol inactive after';
$string['longtimenosee_help'] = 'If users haven\'t accessed a course for a long time, then they are automatically unenrolled. This parameter specifies that time limit.';
$string['maxenrolled'] = 'Max enrolled users';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can self enrol. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to self-enrol was already reached.';
$string['messageprovider:expiry_notification'] = 'By Category enrolment expiry notifications';
$string['newenrols'] = 'Allow new enrolments';
$string['newenrols_desc'] = 'Allow users to self enrol into new courses by default.';
$string['newenrols_help'] = 'This setting determines whether a user can enrol into this course.';
$string['nocategory'] = 'no category limitation';
$string['nocourseincategory'] = 'To be able to enrol you have to complete a course from the "{$a}" category';
$string['nocourseincategorysince'] = 'To be able to enrol you have to complete a course from the "{$a}" category or your last course completion in that category is too much in the past.';
$string['pluginname'] = 'Enrol by Category';
$string['pluginname_desc'] = 'The By Category enrolment plugin allows users participage in a course which requires an ealier successful participation in a course in a specific category. Internally the enrolment is done via the manual enrolment plugin which has to be enabled in the same course.';
$string['role'] = 'Default assigned role';
$string['bycategory:config'] = 'Configure By Category enrol instances';
$string['bycategory:enrolself'] = 'Self enrol in course';
$string['bycategory:passcategory'] = 'Appear as a eligible participant';
$string['bycategory:manage'] = 'Manage enrolled users';
$string['bycategory:unenrol'] = 'Unenrol users from course';
$string['bycategory:unenrolself'] = 'Unenrol self from the course';
$string['sendcoursewelcomemessage'] = 'Send course welcome message';
$string['sendcoursewelcomemessage_help'] = 'When a user enrols in the course, they may be sent a welcome message email. If sent from the course contact (by default the teacher), and more than one user has this role, the email is sent from the first user to be assigned the role.';
$string['sendexpirynotificationstask'] = "By Category enrolment send expiry notifications task";
$string['status'] = 'Allow enrolment';
$string['status_desc'] = 'Enable By Category enrolment method in new courses.';
$string['status_help'] = 'If disabled, this By Category enrolment method is disabled, since all existing By Category enrolments are suspended and new users cannot enrol by category.';
$string['syncenrolmentstask'] = 'Synchronise By Category enrolments task';
$string['unenrol'] = 'Unenrol user';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['unenrolusers'] = 'Unenrol users';
$string['welcometocourse'] = 'Welcome to {$a}';
$string['welcometocoursetext'] = 'Welcome to {$a->coursename}!

If you have not done so already, you should edit your profile page so that we can learn more about you:

  {$a->profileurl}';
$string['privacy:metadata'] = 'The By Category enrolment plugin does not store any personal data.';
