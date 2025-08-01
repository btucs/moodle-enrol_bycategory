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
 * Webservices of enrol bycategory plugin
 *
 * @package   enrol_bycategory
 * @copyright 2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = [
    'enrol_bycategory_get_enrolment_methods' => [
        'classname' => '\enrol_bycategory\external\get_enrolment_methods',
        'description' => 'Fetch enrolment methods (manual, bycategory) for a given course id',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'enrol/bycategory:manage',
        'services' => [],
        // Moodle 3.9.
        'classpath' => 'enrol/bycategory/external/get_enrolment_methods.php',
        'methodname' => 'execute',
    ],
    'enrol_bycategory_get_instance_info' => [
        'classname'   => '\enrol_bycategory\external\get_instance_info',
        'methodname'  => 'execute',
        'classpath'   => 'enrol/bycategory/classes/external/get_instance_info.php',
        'description' => 'enrol_bycategory instance information.',
        'type'        => 'read',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'enrol_bycategory_enrol_user' => [
        'classname'   => '\enrol_bycategory\external\enrol_user',
        'methodname'  => 'execute',
        'classpath'   => 'enrol/bycategory/classes/external/enrol_user.php',
        'description' => 'bycategory enrol the current user in the given course.',
        'type'        => 'write',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
