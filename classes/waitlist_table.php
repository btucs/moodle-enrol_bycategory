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
require_once "$CFG->libdir/tablelib.php";

class enrol_bycategory_waitlist_table extends table_sql {

  private $course;

  function __construct($course, $params = []) {
    global $PAGE;

    parent::__construct('waitlist');

    $this->course = $course;

    $columns = ['firstname', 'lastname', 'email', 'timecreated', 'notified', 'actions'];
    $this->define_columns($columns);

    $headers = [
      get_string('firstname'),
      get_string('lastname'),
      get_string('email'),
      get_string('onwaitlistsince', 'enrol_bycategory'),
      get_string('notifiedcount', 'enrol_bycategory'),
      ''
    ];
    $this->define_headers($headers);

    $this->collapsible(false);
    $this->column_class('actions', 'text-nowrap');
    $this->pageable(true);
    $this->sortable(true, 'lastname', SORT_ASC);

    $where = 'ebw.instanceid = :instanceid';

    $this->set_sql('', '', $where, $params);

    $PAGE->requires->js_call_amd('enrol_bycategory/confirm', 'init');
  }

  public function query_db($pagesize, $useinitialsbar = true) {
    global $DB;

    $sort = $this->get_sql_sort();
    if($sort) {
      $sort = "ORDER BY $sort";
    }

    $where = '';
    if(!empty($this->sql->where)) {
      $where = "WHERE {$this->sql->where}";
    }

    $sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.firstnamephonetic,
                   u.lastnamephonetic, u.middlename, u.alternatename, ebw.timecreated,
                   ebw.notified
              FROM {enrol_bycategory_waitlist} ebw
              JOIN {user} u ON u.id = ebw.userid
              {$where}
              {$sort}";

    $this->pagesize($pagesize, $DB->count_records('enrol_bycategory_waitlist', $this->sql->params));
    if(!$this->is_downloading()) {
      $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
    } else {
      $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
    }
  }

  public function col_timecreated($row) {

    return userdate($row->timecreated, get_string('strftimedatetimeshort', 'langconfig'));
  }

  public function col_actions($row) {
    global $OUTPUT;

    $actions = [];

    $url = new moodle_url('/enrol/bycategory/enrolwaitlistuser.php', [
      'enrolid' => $this->sql->params['instanceid'],
      'uid' => $row->id
    ]);

    $actions[] = $OUTPUT->action_icon(
      $url,
      new pix_icon(
        't/enrol',
        get_string('enrolwaitlistuser', 'enrol_bycategory', [
          'user' => fullname($row, true),
          'course' => format_string($this->course->fullname)
        ]),
        'enrol_bycategory',
      ),
      null,
      [
        'data-action' => 'enrol_bycategory/confirm',
        'data-message' => get_string('enrolwaitlistuserconfirm', 'enrol_bycategory', [
          'user' => fullname($row, true),
          'course' => format_string($this->course->fullname)
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
        'enrol_bycategory',
      ),
      null,
      [
        'data-action' => 'enrol_bycategory/confirm',
        'data-message' => get_string('removewaitlistuserconfirm', 'enrol_bycategory', [
          'user' => fullname($row, true),
          'course' => format_string($this->course->fullname)
        ]),
        'data-sesskey' => sesskey(),
        'class' => 'remove_user'
      ]
    );

    return implode('&nbsp', $actions);
  }
}
