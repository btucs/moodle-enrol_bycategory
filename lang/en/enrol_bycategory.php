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

$string['autogroup'] = 'Users automatically join the specified group';
$string['autogroup_help'] = 'Users enrolling via this enrolment method will also be automatically enroled to the selected group.';
$string['bulkenrol'] = 'Enrol selected into';
$string['bulkenrolconfirmmessage'] = 'Are you sure you want to enrol the following users:

{$a->users}

to the course {$a->coursename} using enrolment method {$a->enrol}?

They will be removed from this waiting list afterwards.';
$string['bulkenrolconfirmtitle'] = 'Confirm selection';
$string['bulkenrolsuccess'] = 'The selected users have been successfully enroled';
$string['bulkenrolusersmissing'] = 'The following users couldn\'t be enroled as they where not part of the waiting list.

{$a}

All other users have been successfully enroled.';
$string['canntenrol'] = 'Enrolment is disabled or inactive';
$string['canntenrolearly'] = 'You cannot enrol yet; enrolment starts on {$a}.';
$string['canntenrollate'] = 'You cannot enrol any more, since enrolment ended on {$a}.';
$string['category'] = 'Course completed in Category';
$string['category_help'] = 'Select the category in which a course has to be completed to be eligible to access this course.

If you select "no category limitation" anyone can enrol.';
$string['confirmbulkdeleteenrolment'] = 'Are you sure you want to delete these user enrolments?';
$string['customwelcomemessage'] = 'Custom welcome message';
$string['customremovedfromwaitlistmessage'] = 'Custom removed from waitlist notification';
$string['customremovedfromwaitlistmessage_help'] = 'A custom notification sent to a student when there were no responsiveness to self-enrol notifications.';
$string['customwaitlistinfomessage'] = 'Custom waitlist info message';
$string['customwaitlistinfomessage_help'] = 'A custom message to display to students once placed on the waitlist';
$string['customselfenrolmessage'] = 'Custom self enrol message';
$string['customselfenrolmessage_help'] = 'A custom self-enrol notification message to waitlist student in plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Course link {$a->courseurl}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}
* User first name {$a->firstname}
* Self enrol link {$a->confirmenrolurl}
* Leave wait list link {$a->leavewaitlisturl}
* Times user notified {$a->usernotifiedcount}
* Total number of notifications {$a->usernotifytotalcount}';
$string['customwelcomemessage_help'] = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Course link {$a->courseurl}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}
* User first name {$a->firstname}';
$string['completionperiod'] = 'Timelimit since completion';
$string['completionperiod_help'] = 'Allowed duration since completing a course in the configured category.';
$string['courseid'] = 'Course ID';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during self enrolment.';
$string['deleteselectedusers'] = 'Delete selected user enrolments';
$string['editselectedusers'] = 'Edit selected user enrolments';
$string['enablewaitlist'] = 'Enable waiting list';
$string['enablewaitlist_help'] = 'When waiting list is enabled users will be added to a waiting list after max enrolled user limit has been reached.';
$string['enrolchancemissed'] = 'Sorry, the spot is already taken. Please try again next time.';
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
$string['enrolwaitlistuser'] = 'Enrol "{$a->user}" into "{$a->course}"';
$string['enrolwaitlistuserconfirm'] = 'Do you really want to manually enrol "{$a->user}" into "{$a->course}"?';
$string['externalvalidation'] = 'External enrolment validation';
$string['externalvalidation_help'] = 'External service URL to validate user enrolment.

The service should accept can pass a moodle user id parameter {$a->id} and should return a JSON string in the format: { "eligibilty":[0|1], "warning":"<<response_message>>" }.';
$string['externalseniorityapi'] = 'External seniority date API';
$string['externalseniorityapi_help'] = 'HTTP service URI to get external seniority date, base URL only (i.e. https://path/to/service/get_pilot_seniority.php?id={$a->id}&field=username)';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'By Category enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'By Category enrolment in the course "{$a->course}" will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course "{$a->course}" is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['expirynotifyall'] = 'Teacher and enrolled user';
$string['expirynotifyenroller'] = 'Teacher only';
$string['joinwaitlist'] = 'Join the waiting list';
$string['joinwaitlistmessage'] = 'You can join the waiting list and will be informed whenever a slot becomes available.';
$string['leavewaitlist'] = 'Leave waiting list';
$string['longtimenosee'] = 'Unenrol inactive after';
$string['longtimenosee_help'] = 'If users haven\'t accessed a course for a long time, then they are automatically unenrolled. This parameter specifies that time limit.';
$string['maxenrolled'] = 'Max enrolled users';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can self enrol. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to self-enrol was already reached.';
$string['messageprovider:expiry_notification'] = 'By Category enrolment expiry notifications';
$string['messageprovider:waitlist_notification'] = 'By Category enrolment waiting list notifications';
$string['newenrols'] = 'Allow new enrolments';
$string['newenrols_desc'] = 'Allow users to self enrol into new courses by default.';
$string['newenrols_help'] = 'This setting determines whether a user can enrol into this course.';
$string['nocategory'] = 'no category limitation';
$string['nocourseincategory'] = 'To be able to enrol you have to complete a course from the "{$a}" category';
$string['nocourseincategorysince'] = 'To be able to enrol you have to complete a course from the "{$a}" category or your last course completion in that category is too much in the past.';
$string['nogroup'] = 'No group limit';
$string['notifiedcount'] = 'Notified without reaction';
$string['onwaitlistsince'] = 'On waiting list since';
$string['pluginname'] = 'Enrol by Category';
$string['pluginname_desc'] = 'The By Category enrolment plugin allows users participate in a course which may require an ealier successful participation in a course in a specific category. Additionally the plugin offers waiting list functionality. Internally the enrolment is done via the manual enrolment plugin which has to be enabled in the same course.';
$string['privacy:metadata'] = 'The By Category enrolment plugin does not store any personal data.';
$string['priority'] = 'Enrolment priority by';
$string['priority_desc'] = 'Priority for waitlist enrolment or seniority';
$string['priority_help'] = 'This setting determines whether prioritize enrolment by waitlist or seniority.';
$string['prioritybyenlisting'] = 'Enlisting';
$string['prioritybyseniority'] = 'Seniority';
$string['removewaitlistuser'] = 'Remove user from waiting list';
$string['removewaitlistuserconfirm'] = 'Do you really want to remove "{$a->user}" from the waiting list of "{$a->course}"?';
$string['role'] = 'Default assigned role';
$string['bycategory:config'] = 'Configure By Category enrol instances';
$string['bycategory:enrolself'] = 'Self enrol in course';
$string['bycategory:manage'] = 'Manage enrolled users';
$string['bycategory:unenrol'] = 'Unenrol users from course';
$string['bycategory:unenrolself'] = 'Unenrol self from the course';
$string['sendcoursewelcomemessage'] = 'Send course welcome message';
$string['sendcoursewelcomemessage_help'] = 'When a user enrols in the course, they may be sent a welcome message email. If sent from the course contact (by default the teacher), and more than one user has this role, the email is sent from the first user to be assigned the role.';
$string['sendexpirynotificationstask'] = "By Category enrolment send expiry notifications task";
$string['sendwaitlistnotificationstask'] = "By Category enrolment send waitlist notifications task";
$string['status'] = 'Allow existing enrolments';
$string['status_desc'] = 'Enable By Category enrolment method in new courses.';
$string['status_help'] = 'If disabled, this By Category enrolment method is disabled, since all existing By Category enrolments are suspended and new users cannot enrol by category.';
$string['syncenrolmentstask'] = 'Synchronise By Category enrolments task';
$string['tokeninvalid'] = 'The provided link is not valid or expired. Please click the link in your email or make sure that you have copied it completly before inserting it into the browser. If your email is older than 24 hours then the link is already expired.';
$string['unabletogetcriteria'] = 'Unable to evaluate external criteria. Please verify the external enrolment validation service is setup properly in enrolment settings.';
$string['unenrol'] = 'Unenrol user';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['unenrolusers'] = 'Unenrol users';
$string['usernotonwaitlist'] = 'You are not on the waiting list of this course.';
$string['waitlist'] = 'Waiting List';
$string['waitlist_active'] = '{$a} user(s) on the waiting list';
$string['waitlist_blocked_message'] = 'You have been notified 5 times about an available slot without reacting.

You will not receive any more notifications.

If you are still interested in joining this course, please leave the waiting list and join again.
This will allow you to receive notifications again, but will also place you at the end of the waiting list.';
$string['waitlist_deactivated'] = 'Waiting list is not active';
$string['waitlist_info_message'] = 'If a slot becomes available you will be informed via e-mail.
Be aware that other persons may be informed as well, so respond as soon as you can.
An e-mail will be sent to you when a slot becomes available.
Please make sure to enable notifications in your user settings and that emails are not sent to your Junk email folder.';
$string['waitlist_position_message'] = '<p>Your current position on the waiting list is: <strong>{$a->waitlistposition}</strong>.</p>

  <p>If you no longer like to wait, you can leave the waitlist by clicking the button below.</p>';
$string['waitlist_users'] = 'Users on waiting list';
$string['waitlistnotifycount'] = 'Number of users to notify about an available slot';
$string['waitlistnotifycount_help'] = 'Notify up to x users on the waiting list when a slot becomes available. The first user to react can enrol into the course. Enter 0 to notify as many users as available slots.';
$string['waitlistnotifylimit'] = 'Amount of times a user is being notified at most';
$string['waitlistnotifylimit_help'] = 'Users are on a specific waiting list are only notified a specific amount of times until they are removed from the waitlist.';
$string['waitlistnotifyperiod'] = 'Amount of days between each enrol reminder';
$string['waitlistnotifyperiod_help'] = 'Days between each notification reminding the user to enrol.';
$string['waitlist_notification_subject'] = '{$a->coursename} - Available spot!';
$string['waitlist_notification_ccsubject'] = 'New waitlisted enrolment notification - {$a->courseshortname} course!';
$string['waitlist_notification_ccbody'] = '<div style="font-family:sans-serif"><strong>Notification {$a->usernotifiedcount} of {$a->usernotifytotalcount}</strong>.<p><br>Waitlisted student \'{$a->userfullname}\' was notified of an open spot in the course: \'{$a->coursename}\' with instructions to self-enrol or opt out.
  <br><br>If \'{$a->userfullname}\' does not self-enrol by the last notification, the spot will be reassigned to another waitlisted student.</p><br>
  <p><a href="{$a->waitlisturl}">{$a->coursename} waitlist</a>.</p></div>';
$string['waitlist_removed_notification_subject'] = '{$a->coursename} - removed from the waitlist!';
$string['waitlist_removed_notification_body'] = '<div style="font-family:sans-serif"><p>Dear {$a->firstname},</p><p>Due to your unresponsiveness in enrolling in the {$a->coursename} course, you have been removed from the waitlist.</p>
  <p>We attempted to reach you on this email address {$a->usernotifytotalcount} times with instructions to self-enrol or opt-out, but no action was taken on your part.</p>
  <p>Best regards,</p><p>&nbsp;</p><p>Training Staff</p>';
$string['waitlist_status_info'] = 'Up to {$a->notifycount} Users from the waiting list are informed about an open spot in this course {$a->nextruntime}.
Users are only informed {$a->notifylimit} times without reaction until they are removed from the waitlist.';
$string['welcometocourse'] = 'Welcome to {$a}';
$string['welcometocoursetext'] = 'Welcome to {$a->coursename}!

If you have not done so already, you should edit your profile page so that we can learn more about you:

  {$a->profileurl}';
$string['wrongtokenuser'] = 'The link was meant for another user. Please wait until you receive your Email.';
