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

use core\event\course_deleted;
use core\event\user_updated;
use core\event\user_enrolment_deleted;

/**
 * Observers
 */
class enrol_bycategory_observers {
    /**
     * Observe course_deleted event
     * @param course_deleted $event
     */
    public static function course_deleted(course_deleted $event) {
        $eventdata = $event->get_data();
    }

    /**
     * Observe user_enrolment_deleted event
     * @param user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(user_enrolment_deleted $event) {
        $eventdata = $event->get_data();
    }

    /**
     * Observe user_updated event if user update is a suspension and delete user from waitlist
     * @param user_updated $event
     */
    public static function user_updated(user_updated $event) {
        global $DB;

        // check if the user is suspended
        $eventdata = (object) $event->get_data();
        $user = $DB->get_record('user', array('id'=>$eventdata->relateduserid));

        if ($user->suspended == 1) {
            $DB->delete_records('enrol_bycategory_waitlist', ['userid' => $eventdata->relateduserid]);
        }
    }
}
