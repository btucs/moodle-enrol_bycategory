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
 * Bycategory enrol plugin implementation.
 *
 * @package     enrol_bycategory
 * @copyright   2023 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Load users based on user ids
 * @param array $userids list of user ids
 * @return array list of users
 */
function enrol_bycategory_get_users_by_id($userids) {
    global $DB;

    list($insql, $inparams) = $DB->get_in_or_equal($userids);
    $users = $DB->get_records_select(
        'user',
        "id {$insql}",
        $inparams,
        'timecreated DESC',
        'id, firstname, lastname, email, firstnamephonetic, lastnamephonetic, middlename, alternatename, timecreated'
    );

    return $users;
}

/**
 * display management view for teachers
 * @param enrol_bycategory_waitlist $waitlist
 * @param stdClass $course
 * @param stdClass $instance enrol instance
 */
function enrol_bycategory_waitlist_show_management_view($waitlist, $course, $instance) {
    global $OUTPUT;

    $download = optional_param('download', '', PARAM_ALPHA);
    $table = new enrol_bycategory_waitlist_table($course, ['instanceid' => $instance->id]);
    $table->is_downloading(
        $download,
        get_string('waitlist_users', 'enrol_bycategory'),
        get_string('waitlist_users', 'enrol_bycategory')
    );

    $url = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $instance->id]);
    $table->define_baseurl($url);
    // Render table ealier to have totalrow filled without doing second request.
    ob_start();
    $table->out(50, true);
    $tableoutput = ob_get_contents();
    ob_end_clean();

    if (!$table->is_downloading()) {
        echo $OUTPUT->header();
        $waitlisttranslation = get_string('waitlist', 'enrol_bycategory');
        $heading = !empty($instance->name) ? "$instance->name - $waitlisttranslation" : $waitlisttranslation;
        echo $OUTPUT->heading($heading);
        if ($table->totalrows > 0) {
            echo $OUTPUT->box(enrol_bycategory_waitlist_show_status_info());
        }
    }

    echo $tableoutput;

    if (!$table->is_downloading()) {
        echo $OUTPUT->footer();
    }
}

/**
 * display user view to students
 * @param enrol_bycategory_waitlist $waitlist
 * @param stdClass $course
 * @param stdClass $instance enrol instance
 */
function enrol_bycategory_waitlist_show_user_view($waitlist, $course, $instance) {
    global $USER, $OUTPUT;

    $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

    if (!$waitlist->is_on_waitlist($USER->id)) {
        redirect($courseurl);
    } else {
        $leavewaitlistexists = optional_param('leavewaitlist', null, PARAM_TEXT);
        if ($leavewaitlistexists !== null) {
            $waitlist->remove_user($USER->id);
            redirect($courseurl);
        }
    }

    $form = new enrol_bycategory_leave_waitlist_form($instance);
    $waitlistposition = $waitlist->get_user_position($USER->id);
    $waitlistinfo = '';
    if ($waitlistposition !== -1) {
        if (!empty($instance->customtext3)) {
            $waitlistinfo = str_replace('{$a->usernotifytotalcount}', get_config('enrol_bycategory', 'waitlistnotifylimit') - 1, $instance->customtext3);
        } else {
            $waitlistinfo = get_string('waitlist_info_message', 'enrol_bycategory');
        }
        $waitlistinfo .= get_string(
            'waitlist_position_message',
            'enrol_bycategory', ['waitlistposition' => $waitlistposition]);
    } else {
        $waitlistinfo = get_string(
            'waitlist_blocked_message',
            'enrol_bycategory'
        );
    }

    $templatecontext = [
        'waitlistinfo' => text_to_html($waitlistinfo, false, false, true),
        'form' => $form->render(),
    ];

    echo $OUTPUT->header();
    $waitlisttranslation = get_string('waitlist', 'enrol_bycategory');
    $heading = !empty($instance->name) ? "$instance->name - $waitlisttranslation" : $waitlisttranslation;
    echo $OUTPUT->heading($heading);
    echo $OUTPUT->render_from_template('enrol_bycategory/waitlist', $templatecontext);
    echo $OUTPUT->footer();
}

/**
 * Delete expired tokens
 * @param int $time
 */
function enrol_bycategory_delete_expired_tokens($time) {
    global $DB;

    $sql = "DELETE FROM {enrol_bycategory_token}
           WHERE timecreated < :time";

    $DB->execute($sql, [
        'time' => $time - 86400,
    ]);
}

/**
 * Show status information about the waitlist. When are users will be informed
 * next time? How many users are informed and how often?
 * @return string HTML
 */
function enrol_bycategory_waitlist_show_status_info() {
    global $PAGE;

    $task = \core\task\manager::get_scheduled_task(\enrol_bycategory\task\send_waitlist_notifications::class);
    $renderer = $PAGE->get_renderer('tool_task');
    $nextruntime = $renderer->next_run_time($task);

    $config = get_config('enrol_bycategory');
    $notifycount = $config->waitlistnotifycount;
    $notifylimit = $config->waitlistnotifylimit;

    $statusdata = [
        'nextruntime' => $nextruntime,
        'notifycount' => $notifycount,
        'notifylimit' => $notifylimit,
    ];

    return text_to_html(get_string('waitlist_status_info', 'enrol_bycategory', $statusdata), false, false, true);
}
