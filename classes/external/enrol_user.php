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
 * enrol_user webservice
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
use external_warnings;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * Webservice to retrieve enrol_bycategory instance info
 */
class enrol_user extends external_api {

    /**
     * Parameters description
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Id of the course'),
                'password' => new external_value(PARAM_RAW, 'Enrolment key', VALUE_DEFAULT, ''),
                'instanceid' => new external_value(PARAM_INT, 'Instance id of self enrolment plugin.', VALUE_DEFAULT, 0),
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
                'status' => new external_value(PARAM_BOOL, 'status: true if the user is enrolled, false otherwise'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Execute the webservice to enroll a user in a course
     *
     * @param  int    $courseid   ID of the course to enroll in
     * @param  string $password   Enrollment key (optional)
     * @param  int    $instanceid ID of the specific enrollment instance (optional)
     * @return array Contains status (boolean) and any warnings
     */
    public static function execute($courseid, $password = '', $instanceid = 0) {
        global $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'password' => $password,
                'instanceid' => $instanceid,
            ]
        );

        $warnings = [];

        $course = get_course($params['courseid']);
        $context = \context_course::instance($course->id);
        self::validate_context(\context_system::instance());

        if (!\core_course_category::can_view_course_info($course)) {
            throw new \moodle_exception('coursehidden');
        }

        // Retrieve the self enrolment plugin.
        $enrol = enrol_get_plugin('bycategory');
        if (empty($enrol)) {
            throw new \moodle_exception('canntenrol', 'enrol_bycategory');
        }

        // We can expect multiple self-enrolment instances.
        $instances = [];
        $enrolinstances = enrol_get_instances($course->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "bycategory") {
                // Instance specified.
                if (!empty($params['instanceid'])) {
                    if ($courseenrolinstance->id == $params['instanceid']) {
                        $instances[] = $courseenrolinstance;
                        break;
                    }
                } else {
                    $instances[] = $courseenrolinstance;
                }

            }
        }
        if (empty($instances)) {
            throw new \moodle_exception('canntenrol', 'enrol_bycategory');
        }

        // Try to enrol the user in the instance/s.
        $enrolled = false;
        foreach ($instances as $instance) {
            $enrolstatus = $enrol->can_self_enrol($instance);
            if ($enrolstatus === true) {
                if ($instance->password && $params['password'] !== $instance->password) {

                    // Check if we are using group enrolment keys.
                    if ($instance->customint1) {
                        require_once($CFG->dirroot . "/enrol/self/locallib.php");

                        if (!enrol_self_check_group_enrolment_key($course->id, $params['password'])) {
                            $warnings[] = [
                                'item' => 'instance',
                                'itemid' => $instance->id,
                                'warningcode' => '2',
                                'message' => get_string('passwordinvalid', 'enrol_bycategory'),
                            ];
                            continue;
                        }
                    } else {
                        if ($enrol->get_config('showhint')) {
                            $hint = \core_text::substr($instance->password, 0, 1);
                            $warnings[] = [
                                'item' => 'instance',
                                'itemid' => $instance->id,
                                'warningcode' => '3',
                                // Message is PARAM_TEXT.
                                'message' => s(get_string('passwordinvalidhint', 'enrol_bycategory', $hint)),
                            ];
                            continue;
                        } else {
                            $warnings[] = [
                                'item' => 'instance',
                                'itemid' => $instance->id,
                                'warningcode' => '4',
                                'message' => get_string('passwordinvalid', 'enrol_bycategory'),
                            ];
                            continue;
                        }
                    }
                }

                // Do the enrolment.
                $data = ['enrolpassword' => $params['password']];

                $enrol->enrol_self($instance, (object) $data);
                $enrolled = true;
                break;
            } else {
                $warnings[] = [
                    'item' => 'instance',
                    'itemid' => $instance->id,
                    'warningcode' => '1',
                    'message' => $enrolstatus,
                ];
            }
        }

        $result = [];
        $result['status'] = $enrolled;
        $result['warnings'] = $warnings;
        return $result;
    }
}
