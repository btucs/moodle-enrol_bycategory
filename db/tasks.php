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
 * Task definition for enrol_bycategory.
 * @copyright 2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 *            based on work by Farhan Karmali <farhan6318@gmail.com>
 * @package   enrol_bycategory
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\enrol_bycategory\task\sync_enrolments',
        'blocking' => 0,
        'minute' => '*/10',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 0,
    ],
    [
        'classname' => '\enrol_bycategory\task\send_expiry_notifications',
        'blocking' => 0,
        'minute' => '*/10',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 0,
    ],
    [
        'classname' => '\enrol_bycategory\task\send_waitlist_notifications',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '14',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabked' => 0,
    ],
];

