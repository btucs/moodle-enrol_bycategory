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
 * Enrol bycategory
 *
 * @package    enrol_bycategory
 * @copyright  2025 Andreas Rosenthal, ssystems GmbH <arosenthal@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$addons = [
  "enrol_bycategory" => [
        "handlers" => [
            'bycategory' => [
                'delegate' => 'CoreEnrolDelegate',
                'enrolmentAction' => 'self',
                'method' => 'mobile_js',
            ],
        ],
        'lang' => [
            ['pluginname', 'enrol_bycategory'],
            ['bycategory:enrolself', 'enrol_bycategory'],
            ['canntenrol', 'enrol_bycategory'],
            ['nopassword', 'enrol_bycategory'],
            ['password', 'enrol_bycategory'],
        ],
    ],
];
