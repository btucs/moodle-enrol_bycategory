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
use core\event\user_enrolment_deleted;

class enrol_bycategory_observers {
    public static function course_deleted(course_deleted $event) {
        $eventdata = $event->get_data();
    }

    public static function user_enrolment_deleted(user_enrolment_deleted $event) {
        $eventdata = $event->get_data();
    }
}
