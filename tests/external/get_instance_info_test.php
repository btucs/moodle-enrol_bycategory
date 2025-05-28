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
 * get_instance_info webservice test
 *
 * @package    enrol_bycategory
 * @copyright  2025 Andreas Rosenthal, ssystems GmbH <arosenthal@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_bycategory\external;

use context_course;
use external_api;
use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Testcase for get_instance_info
 * @covers \enrol_bycategory\external\get_instance_info
 */
final class get_instance_info_test extends externallib_advanced_testcase {
    /**
     * Test the webservice functionality
     * @runInSeparateProcess
     */
    public function test_webservice(): void {
        global $DB, $USER, $CFG;
        $this->resetAfterTest(true);

        $CFG->enrol_plugins_enabled = 'manual,guest,bycategory';

        $course = $this->getDataGenerator()->create_course();
        $USER = $this->getDataGenerator()->create_user();

        $context = \context_course::instance($course->id);
        $this->assignUserCapability('enrol/bycategory:enrolself', $context->id);

        $plugin = enrol_get_plugin('bycategory');
        $this->assertNotEmpty($plugin);

        $instanceid = $plugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'name' => 'Test bycategory',
            'password' => 'testpassword',
            'customint1' => 0,
            'customint6' => 1,
        ]);

        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        $context = context_course::instance($course->id);
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        role_assign($studentrole->id, $USER->id, $context->id);

        $returnvalue = get_instance_info::execute($instance->id);

        $returnvalue = external_api::clean_returnvalue(
            get_instance_info::execute_returns(),
            $returnvalue
        );

        $this->assertEquals($instance->id, $returnvalue['id']);
        $this->assertEquals($course->id, $returnvalue['courseid']);
        $this->assertEquals('bycategory', $returnvalue['type']);
        $this->assertEquals('Test bycategory', $returnvalue['name']);
        $this->assertArrayHasKey('status', $returnvalue);

        $this->assertArrayNotHasKey('enrolpassword', $returnvalue);
    }

    /**
     * Test the webservice with admin privileges
     * @runInSeparateProcess
     */
    public function test_webservice_as_admin(): void {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $CFG->enrol_plugins_enabled = 'manual,guest,bycategory';

        $course = $this->getDataGenerator()->create_course();

        $this->setAdminUser();

        $plugin = enrol_get_plugin('bycategory');
        $this->assertNotEmpty($plugin);

        $instanceid = $plugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'name' => 'Test bycategory',
            'password' => 'secretpassword',
            'customint1' => 0,
            'customint6' => 1,
        ]);

        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        $returnvalue = get_instance_info::execute($instance->id);

        $returnvalue = external_api::clean_returnvalue(
            get_instance_info::execute_returns(),
            $returnvalue
        );

        $this->assertEquals($instance->id, $returnvalue['id']);
        $this->assertEquals($course->id, $returnvalue['courseid']);
        $this->assertEquals('bycategory', $returnvalue['type']);
        $this->assertEquals('Test bycategory', $returnvalue['name']);
        $this->assertArrayHasKey('status', $returnvalue);
    }
}
