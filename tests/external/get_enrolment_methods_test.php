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
 * get_enrolment_methods webservice test
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_bycategory\external;

use context_course;
use external_api;
use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Testcase for get_enrolment_methods
 * @covers \enrol_bycategory\external\get_enrolment_methods
 */
class get_enrolment_methods_test extends externallib_advanced_testcase {
    /**
     * Test the webservice functionality
     * @runInSeparateProcess
     */
    public function test_webservice() {
        global $DB, $USER;
        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $USER = $this->getDataGenerator()->create_user();

        $context = context_course::instance($course->id);

        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $manualplugin = enrol_get_plugin('manual');

        $maninstance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $manualplugin->enrol_user($maninstance, $USER->id, $teacherrole->id);

        $this->assignUserCapability('enrol/bycategory:manage', $context->id, $teacherrole->id);
        $returnvalue = get_enrolment_methods::execute($course->id);

        $returnvalue = external_api::clean_returnvalue(
            get_enrolment_methods::execute_returns(),
            $returnvalue,
        );

        $this->assertEquals(1, count($returnvalue));
        $this->assertEquals($maninstance->id, $returnvalue[0]['id']);
    }
}
