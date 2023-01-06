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
 * get_enrolment_methods webservice
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_bycategory\external;

use context_course;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * Webservice to retrieve enrolment methods (manual, bycategory) from a course
 */
class get_enrolment_methods extends external_api {
    /**
     * Parameters description
     * @return external_description
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ]);
    }

    /**
     * Return parameters description
     * @return external_description
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'enrolment id'),
                'name' => new external_value(PARAM_TEXT, 'name of the enrolment method'),
            ])
        );
    }

    /**
     * Execute the webservice
     * @param int $courseid
     * @return array array of enrolment methods {id,name}
     */
    public static function execute($courseid) {

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);

        $course = get_course($courseid);
        $context = context_course::instance($course->id, MUST_EXIST);
        self::validate_context($context);
        require_capability('enrol/bycategory:manage', $context);

        $enrolinstances = enrol_get_instances($course->id, true);
        $returninstances = [];

        $allowedenrolmethods = ['manual', 'bycategory'];

        foreach ($enrolinstances as $instance) {
            if (in_array($instance->enrol, $allowedenrolmethods)) {
                $name = empty($instance->name) ? get_string('pluginname', 'enrol_' . $instance->enrol) : $instance->name;

                array_push($returninstances, [
                'id' => $instance->id,
                'name' => external_format_string($name, $context),
                ]);
            }
        }

        return $returninstances;
    }
}
