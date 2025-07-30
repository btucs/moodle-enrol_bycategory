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
 * enrol_user webservice test
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
 * Testcase for enrol_user
 * @covers \enrol_bycategory\external\enrol_user
 */
final class enrol_user_test extends externallib_advanced_testcase {
    /**
     * Test the webservice functionality with successful enrollment
     * @runInSeparateProcess
     */
    public function test_webservice_successful_enrollment(): void {
        global $DB, $USER, $CFG;
        $this->resetAfterTest(true);

        $CFG->enrol_plugins_enabled = 'manual,guest,bycategory';
        set_config('enrol_plugins_enabled', 'manual,guest,bycategory');

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
            'enrolperiod' => 0,
        ]);

        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        try {
            $returnvalue = enrol_user::execute($course->id, 'testpassword', $instance->id);

            $returnvalue = external_api::clean_returnvalue(
                enrol_user::execute_returns(),
                $returnvalue
            );
        } catch (\Exception $e) {
            $this->fail('Exception thrown: ' . $e->getMessage());
        }

        $this->assertTrue($returnvalue['status']);
        $this->assertEmpty($returnvalue['warnings']);

        $this->assertTrue(is_enrolled(context_course::instance($course->id), $USER));
    }

    /**
     * Test the webservice functionality with invalid password
     * @runInSeparateProcess
     */
    public function test_webservice_invalid_password(): void {
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
            'password' => 'correctpassword',
            'customint1' => 0,
            'customint6' => 1,
        ]);

        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        $returnvalue = enrol_user::execute($course->id, 'wrongpassword', $instance->id);

        $returnvalue = external_api::clean_returnvalue(
            enrol_user::execute_returns(),
            $returnvalue
        );

        $this->assertFalse($returnvalue['status']);
        $this->assertNotEmpty($returnvalue['warnings']);
        $this->assertEquals('instance', $returnvalue['warnings'][0]['item']);
        $this->assertEquals($instance->id, $returnvalue['warnings'][0]['itemid']);
        $this->assertEquals('4', $returnvalue['warnings'][0]['warningcode']);

        $this->assertFalse(is_enrolled(context_course::instance($course->id), $USER));
    }

    /**
     * Test the webservice with no specific instance
     * @runInSeparateProcess
     */
    public function test_webservice_no_instance_specified(): void {
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

        $returnvalue = enrol_user::execute($course->id, 'testpassword');

        $returnvalue = external_api::clean_returnvalue(
            enrol_user::execute_returns(),
            $returnvalue
        );

        $this->assertTrue($returnvalue['status']);
        $this->assertEmpty($returnvalue['warnings']);

        $this->assertTrue(is_enrolled(context_course::instance($course->id), $USER));
    }

    /**
     * Test the webservice with password hint
     * @runInSeparateProcess
     */
    public function test_webservice_password_with_hint(): void {
        global $DB, $USER, $CFG;
        $this->resetAfterTest(true);

        $CFG->enrol_plugins_enabled = 'manual,guest,bycategory';

        $course = $this->getDataGenerator()->create_course();
        $USER = $this->getDataGenerator()->create_user();

        $context = \context_course::instance($course->id);
        $this->assignUserCapability('enrol/bycategory:enrolself', $context->id);

        set_config('showhint', 1, 'enrol_bycategory');

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

        $returnvalue = enrol_user::execute($course->id, 'wrongpassword', $instance->id);

        $returnvalue = external_api::clean_returnvalue(
            enrol_user::execute_returns(),
            $returnvalue
        );

        $this->assertFalse($returnvalue['status']);
        $this->assertNotEmpty($returnvalue['warnings']);
        $this->assertEquals('instance', $returnvalue['warnings'][0]['item']);
        $this->assertEquals($instance->id, $returnvalue['warnings'][0]['itemid']);
        $this->assertEquals('3', $returnvalue['warnings'][0]['warningcode']);

        $this->assertStringContainsString('s', $returnvalue['warnings'][0]['message']);
    }
}
