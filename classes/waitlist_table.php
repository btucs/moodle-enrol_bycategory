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
 * Table implementation to show users on the waiting list.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->libdir/enrollib.php");

/**
 * Waiting list table
 */
class enrol_bycategory_waitlist_table extends table_sql {

    /** @var stdClass */
    private $course;

    /**
     * Constructor
     * @param stdClass $course course instance
     * @param array $params map of parameters
     */
    public function __construct($course, $params = []) {
        global $PAGE;

        parent::__construct('waitlist');

        $this->course = $course;
        $instances = array_values(enrol_get_instances($course->id, true));
        $instance = $instances[array_search('bycategory', array_column($instances, 'enrol'))];
        $byseniority = !empty($instance->customint8);

        $columns = ['select', 'seq', 'full_name', 'email', 'timecreated'];

        if ($byseniority) {
            $columns[] = 'senioritydate';
        }
        $columns = array_merge($columns, ['notified', 'actions']);
        $this->define_columns($columns);

        $checkboxattrs = [
            'title' => get_string('selectall'),
            'data-action' => 'enrol_bycategory/selectall',
            'autocomplete' => 'off',
        ];

        $headers = [
            html_writer::checkbox('selectall', 1, false, null, $checkboxattrs),
            '#',
            get_string('fullname'),
            get_string('email'),
            get_string('onwaitlistsince', 'enrol_bycategory'),
        ];
        if ($byseniority) {
            $headers[] = get_string('prioritybyseniority', 'enrol_bycategory');
        }
        $headers = array_merge($headers,[
            get_string('notifiedcount', 'enrol_bycategory'),
            '',
        ]);
        $this->define_headers($headers);

        $this->collapsible(false);
        $this->column_class('actions', 'text-nowrap');
        $this->pageable(true);
        $this->sortable(true, ($byseniority ? 'senioritydate' : 'timecreated'));
        $this->no_sorting('select', 'actions');

        $where = 'ebw.instanceid = :instanceid';

        $this->set_sql('', '', $where, $params);

        $PAGE->requires->js_call_amd('enrol_bycategory/confirm', 'init');
        $PAGE->requires->js_call_amd('enrol_bycategory/select-all', 'init');
        $PAGE->requires->js_call_amd('enrol_bycategory/enrol-select', 'init', [intval($params['instanceid'], 10)]);
    }

    /**
     * Query DB to retrieve data for the table
     * @param int $pagesize
     * @param bool $useinitialsbar
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY $sort";
        }

        $where = '';
        if (!empty($this->sql->where)) {
            $where = "WHERE {$this->sql->where}";
        }

        $sql = "SELECT @rownum:=@rownum+1 seq, u.id, concat(u.firstname, \" \", u.lastname, \" \", u.alternatename) as full_name,
                   u.lastname, u.firstname, u.email, u.firstnamephonetic,
                   u.lastnamephonetic, u.middlename, u.alternatename, ebw.timecreated,
                   ebw.notified, ebw.senioritydate
              FROM {enrol_bycategory_waitlist} ebw
              JOIN {user} u ON u.id = ebw.userid, (SELECT @rownum:=0) r
              {$where}
              {$sort}";

        $this->pagesize($pagesize, $DB->count_records('enrol_bycategory_waitlist', $this->sql->params));
        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    /**
     * The select column.
     *
     * @param stdClass $row the row data.
     * @return string;
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public function col_select($row) {

        return \html_writer::checkbox('userids[]', $row->id, false, '', [
            'class' => 'selectuserids',
            'autocomplete' => 'off',
        ]);
    }

    /**
     * The fullname column.
     *
     * @param stdClass $row the row data.
     * @return string;
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public function col_full_name($row) {
        global $OUTPUT;

        $name = fullname($row, has_capability('moodle/site:viewfullnames', $this->get_context()));
        if ($this->download) {
            return $name;
        }

        $profileurl = new moodle_url('/user/profile.php', array('id' => $row->{$this->useridfield}));
        return $OUTPUT->action_link($profileurl, $name);
    }

    /**
     * The timecreated column.
     *
     * @param stdClass $row the row data.
     * @return string;
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public function col_timecreated($row) {

        return userdate($row->timecreated, get_string('strftimedatetimeshort', 'langconfig'));
    }

    /**
     * The seniority date column.
     *
     * @param stdClass $row the row data.
     * @return string;
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public function col_senioritydate($row) {

        return !empty($row->senioritydate) ? userdate($row->senioritydate, get_string('strftimedatetimeshort', 'langconfig')) : '';
    }

    /**
     * The actions column.
     *
     * @param stdClass $row the row data.
     * @return string;
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public function col_actions($row) {
        global $OUTPUT;

        $actions = [];

        $url = new moodle_url('/enrol/bycategory/enrolwaitlistuser.php', [
            'enrolid' => $this->sql->params['instanceid'],
            'uid' => $row->id,
        ]);

        $actions[] = $OUTPUT->action_icon(
            $url,
            new pix_icon(
                't/enrol',
                get_string('enrolwaitlistuser', 'enrol_bycategory', [
                    'user' => fullname($row, true),
                    'course' => format_string($this->course->fullname),
                ]),
                'enrol_bycategory'
            ),
            null,
            [
                'data-action' => 'enrol_bycategory/confirm',
                'data-message' => get_string('enrolwaitlistuserconfirm', 'enrol_bycategory', [
                    'user' => fullname($row, true),
                    'course' => format_string($this->course->fullname),
                ]),
                'data-sesskey' => sesskey(),
                'class' => 'enrol_user',
            ]
        );

        $url = new moodle_url('/enrol/bycategory/removewaitlistuser.php', [
            'enrolid' => $this->sql->params['instanceid'],
            'uid' => $row->id,
        ]);

        $actions[] = $OUTPUT->action_icon(
            $url,
            new pix_icon(
                't/remove',
                get_string('removewaitlistuser', 'enrol_bycategory'),
                'enrol_bycategory'
            ),
            null,
            [
                'data-action' => 'enrol_bycategory/confirm',
                'data-message' => get_string('removewaitlistuserconfirm', 'enrol_bycategory', [
                    'user' => fullname($row, true),
                    'course' => format_string($this->course->fullname),
                ]),
                'data-sesskey' => sesskey(),
                'class' => 'remove_user',
            ]
        );

        return implode('&nbsp', $actions);
    }

    /**
     * Hook to wrap a table in a form
     */
    public function wrap_html_start() {
        echo html_writer::start_tag('form', [
            'action' => new moodle_url('/enrol/bycategory/bulkenrolwaitlistusers.php'),
            'method' => 'POST',
        ]);
        echo html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'enrolid',
            'value' => $this->sql->params['instanceid'],
        ]);
    }

    /**
     * Override the table's wrap_html_finish method in order to render the bulk actions and
     * records per page options.
     */
    public function wrap_html_finish() {
        global $OUTPUT, $DB;

        $sql = "SELECT id FROM {course}
                 WHERE visible = '1' AND (enddate = 0 OR enddate > :enddate)";

        $activecourses = $DB->get_records_sql($sql, [
            'enddate' => time(),
        ]);

        // Preselect current course.
        $mapselected = function($current) {
            $current->selected = $current->id == $this->course->id;

            return $current;
        };

        $data = new stdClass();
        $data->options = array_map(
            $mapselected,
            array_values(
                enrol_get_my_courses(null, 'c.fullname', 0, array_keys($activecourses), true)
            )
        );

        echo $OUTPUT->render_from_template('enrol_bycategory/waitlist_bulk_actions', $data);
        echo html_writer::end_tag('form');
    }
}
