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
 * get_instance_info webservice
 *
 * @package   enrol_bycategory
 * @copyright 2025 Andreas Rosenthal, ssystems GmbH <arosenthal@ssystems.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
 * Webservice to retrieve enrol_bycategory instance info
 */
class get_instance_info extends external_api {

    /**
     * Parameters description
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'instanceid' => new external_value(PARAM_INT, 'instance id of bycategory enrolment plugin.'),
            ]
        );
    }

    /**
     * Return parameters description
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'id of course enrolment instance'),
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'type' => new external_value(PARAM_PLUGIN, 'type of enrolment plugin'),
                'name' => new external_value(PARAM_RAW, 'name of enrolment plugin'),
                'status' => new external_value(PARAM_RAW, 'status of enrolment plugin'),
                'enrolpassword' => new external_value(PARAM_RAW, 'password required for enrolment', VALUE_OPTIONAL),
            ]
        );
    }

    /**
     * Execute the webservice to retrieve enrollment instance information
     *
     * @param  int $instanceid ID of the enrollment plugin instance
     * @return array Information about the enrollment instance
     */
    public static function execute($instanceid) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(self::execute_parameters(), ['instanceid' => $instanceid]);

        // Retrieve self enrolment plugin.
        $enrolplugin = enrol_get_plugin('bycategory');
        if (empty($enrolplugin)) {
            throw new \moodle_exception('invaliddata', 'error');
        }

        self::validate_context(\context_system::instance());

        $enrolinstance = $DB->get_record('enrol', ['id' => $params['instanceid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $enrolinstance->courseid], '*', MUST_EXIST);
        if (!\core_course_category::can_view_course_info($course) && !can_access_course($course)) {
            throw new \moodle_exception('coursehidden');
        }

        $instanceinfo = (array) $enrolplugin->get_enrol_info($enrolinstance);
        if (isset($instanceinfo['requiredparam']->enrolpassword)) {
            $instanceinfo['enrolpassword'] = $instanceinfo['requiredparam']->enrolpassword;
        }
        unset($instanceinfo->requiredparam);

        return $instanceinfo;
    }
}
