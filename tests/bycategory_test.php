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
 * bycategory enrolment plugin tests.
 *
 * @package    enrol_bycategory
 * @category   test
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 *             based on work by 2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_bycategory;

use enrol_bycategory_phpunit_util;
use Exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/lib/setuplib.php');
require_once($CFG->dirroot.'/enrol/bycategory/lib.php');
require_once($CFG->dirroot.'/enrol/bycategory/locallib.php');
require_once(__DIR__ . '/util.php');

/**
 * Testcase for enrol_bycategory_plugin
 * @covers \enrol_bycategory_plugin
 */
class bycategory_test extends \advanced_testcase {
    public function test_basics() {
        $this->assertFalse(enrol_is_enabled('bycategory'));
        $plugin = enrol_get_plugin('bycategory');
        $this->assertInstanceOf('enrol_bycategory_plugin', $plugin);
        $this->assertEquals(1, get_config('enrol_bycategory', 'defaultenrol'));
        $this->assertEquals(ENROL_EXT_REMOVED_KEEP, get_config('enrol_bycategory', 'expiredaction'));
    }

    public function test_sync_nothing() {
        global $SITE;

        $plugin = enrol_get_plugin('bycategory');

        $trace = new \null_progress_trace();

        // Just make sure the sync does not throw any errors when nothing to do.
        $plugin->sync($trace, null);
        $plugin->sync($trace, $SITE->id);
    }

    public function test_longtimenosee() {
        global $DB, $CFG;
        $this->resetAfterTest();

        $plugin = enrol_get_plugin('bycategory');
        $manualplugin = enrol_get_plugin('manual');

        $this->assertNotEmpty($manualplugin);
        $now = time();

        $trace = new \progress_trace_buffer(new \text_progress_trace(), false);
        enrol_bycategory_phpunit_util::enable_plugin();

        // Prepare some data.

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $this->assertNotEmpty($teacherrole);

        $record = ['firstaccess' => $now - 60 * 60 * 24 * 800];
        $record['lastaccess'] = $now - 60 * 60 * 24 * 100;
        $user1 = $this->getDataGenerator()->create_user($record);
        $record['lastaccess'] = $now - 60 * 60 * 24 * 10;
        $user2 = $this->getDataGenerator()->create_user($record);
        $record['lastaccess'] = $now - 60 * 60 * 24 * 1;
        $user3 = $this->getDataGenerator()->create_user($record);
        $record['lastaccess'] = $now - 10;
        $user4 = $this->getDataGenerator()->create_user($record);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $context1 = \context_course::instance($course1->id);
        $context2 = \context_course::instance($course2->id);
        $context3 = \context_course::instance($course3->id);

        $this->assertEquals(3, $DB->count_records('enrol', ['enrol' => 'bycategory']));
        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $id = $plugin->add_instance($course3, ['status' => ENROL_INSTANCE_ENABLED, 'roleid' => $teacherrole->id]);
        $instance3b = $DB->get_record('enrol', ['id' => $id], '*', MUST_EXIST);
        unset($id);

        $this->assertEquals($studentrole->id, $instance1->roleid);
        $instance1->customint2 = 60 * 60 * 24 * 14; // 2 weeks .
        $DB->update_record('enrol', $instance1);
        $plugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $plugin->enrol_user($instance1, $user2->id, $studentrole->id);
        $plugin->enrol_user($instance1, $user3->id, $studentrole->id);
        $this->assertEquals(3, $DB->count_records('user_enrolments'));
        $DB->insert_record('user_lastaccess', [
            'userid' => $user2->id, 'courseid' => $course1->id,
            'timeaccess' => $now - 60 * 60 * 24 * 20,  // ... now - 2 weeks, 6 days .
        ]);
        $DB->insert_record('user_lastaccess', [
            'userid' => $user3->id, 'courseid' => $course1->id,
            'timeaccess' => $now - 60 * 60 * 24 * 2, // ... now - 2 days .
        ]);
        $DB->insert_record('user_lastaccess', ['userid' => $user4->id, 'courseid' => $course1->id, 'timeaccess' => $now - 60]);

        $this->assertEquals($studentrole->id, $instance3->roleid);
        $instance3->customint2 = 60 * 60 * 24 * 50; // 1 month 2 weeks .
        $DB->update_record('enrol', $instance3);
        $plugin->enrol_user($instance3, $user1->id, $studentrole->id);
        $plugin->enrol_user($instance3, $user2->id, $studentrole->id);
        $plugin->enrol_user($instance3, $user3->id, $studentrole->id);
        $plugin->enrol_user($instance3b, $user1->id, $teacherrole->id);
        $plugin->enrol_user($instance3b, $user4->id, $teacherrole->id);
        $this->assertEquals(8, $DB->count_records('user_enrolments'));
        $DB->insert_record('user_lastaccess', [
            'userid' => $user2->id, 'courseid' => $course3->id,
            'timeaccess' => $now - 60 * 60 * 24 * 11, // ... now - 1 week, 4 days .
        ]);

        $DB->insert_record('user_lastaccess', [
            'userid' => $user3->id, 'courseid' => $course3->id,
            'timeaccess' => $now - 60 * 60 * 24 * 200, // ... now - 6 month, 2 weeks .
        ]);
        $DB->insert_record('user_lastaccess', [
            'userid' => $user4->id, 'courseid' => $course3->id,
            'timeaccess' => $now - 60 * 60 * 24 * 200,
        ]);

        $maninstance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'manual'], '*', MUST_EXIST);

        $manualplugin->enrol_user($maninstance2, $user1->id, $studentrole->id);
        $manualplugin->enrol_user($maninstance3, $user1->id, $teacherrole->id);

        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(7, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(2, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));

        // Execute sync - this is the same thing used from cron.

        $plugin->sync($trace, $course2->id);
        $output = $trace->get_buffer();
        $trace->reset_buffer();
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertStringContainsString('No expired enrol_bycategory enrolments detected', $output);
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance1->id, 'userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance1->id, 'userid' => $user2->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance3->id, 'userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $instance3->id, 'userid' => $user3->id]));

        $plugin->sync($trace, null);
        $output = $trace->get_buffer();
        $trace->reset_buffer();
        $this->assertEquals(6, $DB->count_records('user_enrolments'));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance1->id, 'userid' => $user1->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance1->id, 'userid' => $user2->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance3->id, 'userid' => $user1->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance3->id, 'userid' => $user3->id]));
        $this->assertStringContainsString('unenrolling user ' . $user1->id . ' from course ' . $course1->id .
            ' as they did not log in for at least 14 days', $output);
        $this->assertStringContainsString('unenrolling user ' . $user1->id . ' from course ' . $course3->id .
            ' as they did not log in for at least 50 days', $output);
        $this->assertStringContainsString('unenrolling user ' . $user2->id . ' from course ' . $course1->id .
            ' as they did not access the course for at least 14 days', $output);
        $this->assertStringContainsString('unenrolling user ' . $user3->id . ' from course ' . $course3->id .
            ' as they did not access the course for at least 50 days', $output);
        $this->assertStringNotContainsString('unenrolling user ' . $user4->id, $output);

        $this->assertEquals(6, $DB->count_records('role_assignments'));
        $this->assertEquals(4, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(2, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));
    }

    public function test_expired() {
        global $DB;
        $this->resetAfterTest();

        $plugin = enrol_get_plugin('bycategory');
        $manualplugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manualplugin);

        $now = time();

        $trace = new \null_progress_trace();
        enrol_bycategory_phpunit_util::enable_plugin();

        // Prepare some data.

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->assertNotEmpty($managerrole);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $context1 = \context_course::instance($course1->id);
        $context2 = \context_course::instance($course2->id);
        $context3 = \context_course::instance($course3->id);

        $this->assertEquals(3, $DB->count_records('enrol', ['enrol' => 'bycategory']));
        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $this->assertEquals($studentrole->id, $instance1->roleid);
        $instance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $this->assertEquals($studentrole->id, $instance2->roleid);
        $instance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $this->assertEquals($studentrole->id, $instance3->roleid);
        $id = $plugin->add_instance($course3, ['status' => ENROL_INSTANCE_ENABLED, 'roleid' => $teacherrole->id]);
        $instance3b = $DB->get_record('enrol', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals($teacherrole->id, $instance3b->roleid);
        unset($id);

        $maninstance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'manual'], '*', MUST_EXIST);

        $manualplugin->enrol_user($maninstance2, $user1->id, $studentrole->id);
        $manualplugin->enrol_user($maninstance3, $user1->id, $teacherrole->id);

        $this->assertEquals(2, $DB->count_records('user_enrolments'));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));

        $plugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $plugin->enrol_user($instance1, $user2->id, $studentrole->id);
        $plugin->enrol_user($instance1, $user3->id, $studentrole->id, 0, $now - 60);

        $plugin->enrol_user($instance3, $user1->id, $studentrole->id, 0, 0);
        $plugin->enrol_user($instance3, $user2->id, $studentrole->id, 0, $now - 60 * 60);
        $plugin->enrol_user($instance3, $user3->id, $studentrole->id, 0, $now + 60 * 60);
        $plugin->enrol_user($instance3b, $user1->id, $teacherrole->id, $now - 60 * 60 * 24 * 7, $now - 60);
        $plugin->enrol_user($instance3b, $user4->id, $teacherrole->id);

        role_assign($managerrole->id, $user3->id, $context1->id);

        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(7, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(2, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));

        // Execute tests.

        $this->assertEquals(ENROL_EXT_REMOVED_KEEP, $plugin->get_config('expiredaction'));
        $plugin->sync($trace, null);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));

        $plugin->set_config('expiredaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $plugin->sync($trace, $course2->id);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));

        $plugin->sync($trace, null);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(7, $DB->count_records('role_assignments'));
        $this->assertEquals(5, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));
        $this->assertFalse($DB->record_exists('role_assignments', [
            'contextid' => $context1->id, 'userid' => $user3->id, 'roleid' => $studentrole->id,
        ]));
        $this->assertFalse($DB->record_exists('role_assignments', [
            'contextid' => $context3->id, 'userid' => $user2->id, 'roleid' => $studentrole->id,
        ]));
        $this->assertFalse($DB->record_exists('role_assignments', [
            'contextid' => $context3->id, 'userid' => $user1->id, 'roleid' => $teacherrole->id,
        ]));
        $this->assertTrue($DB->record_exists('role_assignments', [
            'contextid' => $context3->id, 'userid' => $user1->id, 'roleid' => $studentrole->id,
        ]));

        $plugin->set_config('expiredaction', ENROL_EXT_REMOVED_UNENROL);

        role_assign($studentrole->id, $user3->id, $context1->id);
        role_assign($studentrole->id, $user2->id, $context3->id);
        role_assign($teacherrole->id, $user1->id, $context3->id);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(7, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(2, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));

        $plugin->sync($trace, null);
        $this->assertEquals(7, $DB->count_records('user_enrolments'));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance1->id, 'userid' => $user3->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance3->id, 'userid' => $user2->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance3b->id, 'userid' => $user1->id]));
        $this->assertEquals(6, $DB->count_records('role_assignments'));
        $this->assertEquals(5, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));
    }

    public function test_send_expiry_notifications() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->preventResetByRollback(); // Messaging does not like transactions...

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');
        $manualplugin = enrol_get_plugin('manual');
        $now = time();
        $admin = get_admin();

        $trace = new \null_progress_trace();

        // Note: hopefully nobody executes the unit tests the last second before midnight...

        $plugin->set_config('expirynotifylast', $now - 60 * 60 * 24);
        $plugin->set_config('expirynotifyhour', 0);

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->assertNotEmpty($editingteacherrole);
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->assertNotEmpty($managerrole);

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'xuser1']);
        $user2 = $this->getDataGenerator()->create_user(['lastname' => 'xuser2']);
        $user3 = $this->getDataGenerator()->create_user(['lastname' => 'xuser3']);
        $user4 = $this->getDataGenerator()->create_user(['lastname' => 'xuser4']);
        $user5 = $this->getDataGenerator()->create_user(['lastname' => 'xuser5']);
        $user6 = $this->getDataGenerator()->create_user(['lastname' => 'xuser6']);
        $user7 = $this->getDataGenerator()->create_user(['lastname' => 'xuser6']);
        $user8 = $this->getDataGenerator()->create_user(['lastname' => 'xuser6']);

        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse1']);
        $course2 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse2']);
        $course3 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse3']);
        $course4 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse4']);

        $this->assertEquals(4, $DB->count_records('enrol', ['enrol' => 'manual']));
        $this->assertEquals(4, $DB->count_records('enrol', ['enrol' => 'bycategory']));

        $maninstance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->expirythreshold = 60 * 60 * 24 * 4;
        $instance1->expirynotify = 1;
        $instance1->notifyall = 1;
        $instance1->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance1);

        $maninstance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance2->expirythreshold = 60 * 60 * 24 * 1;
        $instance2->expirynotify = 1;
        $instance2->notifyall = 1;
        $instance2->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance2);

        $maninstance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $instance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance3->expirythreshold = 60 * 60 * 24 * 1;
        $instance3->expirynotify = 1;
        $instance3->notifyall = 0;
        $instance3->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance3);

        $maninstance4 = $DB->get_record('enrol', ['courseid' => $course4->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $instance4 = $DB->get_record('enrol', ['courseid' => $course4->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance4->expirythreshold = 60 * 60 * 24 * 1;
        $instance4->expirynotify = 0;
        $instance4->notifyall = 0;
        $instance4->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance4);

        // phpcs:disable moodle.Files.LineLength.TooLong
        $plugin->enrol_user($instance1, $user1->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1, ENROL_USER_SUSPENDED); // Suspended users are not notified.
        $plugin->enrol_user($instance1, $user2->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 5);                 // Above threshold are not notified.
        $plugin->enrol_user($instance1, $user3->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 3 + 60 * 60);       // Less than one day after threshold - should be notified.
        $plugin->enrol_user($instance1, $user4->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 4 - 60 * 3);        // Less than one day after threshold - should be notified.
        $plugin->enrol_user($instance1, $user5->id, $studentrole->id, 0, $now + 60 * 60);                          // Should have been already notified.
        $plugin->enrol_user($instance1, $user6->id, $studentrole->id, 0, $now - 60);                               // Already expired.
        $manualplugin->enrol_user($maninstance1, $user7->id, $editingteacherrole->id);
        $manualplugin->enrol_user($maninstance1, $user8->id, $managerrole->id);                                    // Highest role --> enroller.

        $plugin->enrol_user($instance2, $user1->id, $studentrole->id);
        $plugin->enrol_user($instance2, $user2->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1 + 60 * 3);        // Above threshold are not notified.
        $plugin->enrol_user($instance2, $user3->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1 - 60 * 60);       // Less than one day after threshold - should be notified.

        $manualplugin->enrol_user($maninstance3, $user1->id, $editingteacherrole->id);
        $plugin->enrol_user($instance3, $user2->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1 + 60);            // Above threshold are not notified.
        $plugin->enrol_user($instance3, $user3->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1 - 60 * 60);       // Less than one day after threshold - should be notified.

        $manualplugin->enrol_user($maninstance4, $user4->id, $editingteacherrole->id);
        $plugin->enrol_user($instance4, $user5->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1 + 60);
        $plugin->enrol_user($instance4, $user6->id, $studentrole->id, 0, $now + 60 * 60 * 24 * 1 - 60 * 60);
        // phpcs:enable moodle.Files.LineLength.TooLong

        /* The notification is sent out in fixed order first individual users,
           then summary per course by enrolid, user lastname, etc.
        */
        $this->assertGreaterThan($instance1->id, $instance2->id);
        $this->assertGreaterThan($instance2->id, $instance3->id);

        $sink = $this->redirectMessages();

        $plugin->send_expiry_notifications($trace);

        $messages = $sink->get_messages();

        $this->assertEquals(2 + 1 + 1 + 1 + 1 + 0, count($messages));

        // First individual notifications from course1.
        $this->assertEquals($user3->id, $messages[0]->useridto);
        $this->assertEquals($user8->id, $messages[0]->useridfrom);
        $this->assertStringContainsString('xcourse1', $messages[0]->fullmessagehtml);

        $this->assertEquals($user4->id, $messages[1]->useridto);
        $this->assertEquals($user8->id, $messages[1]->useridfrom);
        $this->assertStringContainsString('xcourse1', $messages[1]->fullmessagehtml);

        // Then summary for course1.
        $this->assertEquals($user8->id, $messages[2]->useridto);
        $this->assertEquals($admin->id, $messages[2]->useridfrom);
        $this->assertStringContainsString('xcourse1', $messages[2]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser1', $messages[2]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser2', $messages[2]->fullmessagehtml);
        $this->assertStringContainsString('xuser3', $messages[2]->fullmessagehtml);
        $this->assertStringContainsString('xuser4', $messages[2]->fullmessagehtml);
        $this->assertStringContainsString('xuser5', $messages[2]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser6', $messages[2]->fullmessagehtml);

        // First individual notifications from course2.
        $this->assertEquals($user3->id, $messages[3]->useridto);
        $this->assertEquals($admin->id, $messages[3]->useridfrom);
        $this->assertStringContainsString('xcourse2', $messages[3]->fullmessagehtml);

        // Then summary for course2.
        $this->assertEquals($admin->id, $messages[4]->useridto);
        $this->assertEquals($admin->id, $messages[4]->useridfrom);
        $this->assertStringContainsString('xcourse2', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser1', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser2', $messages[4]->fullmessagehtml);
        $this->assertStringContainsString('xuser3', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser4', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser5', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser6', $messages[4]->fullmessagehtml);

        // Only summary in course3.
        $this->assertEquals($user1->id, $messages[5]->useridto);
        $this->assertEquals($admin->id, $messages[5]->useridfrom);
        $this->assertStringContainsString('xcourse3', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser1', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser2', $messages[5]->fullmessagehtml);
        $this->assertStringContainsString('xuser3', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser4', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser5', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser6', $messages[5]->fullmessagehtml);

        // Make sure that notifications are not repeated.
        $sink->clear();

        $plugin->send_expiry_notifications($trace);
        $this->assertEquals(0, $sink->count());

        // Use invalid notification hour to verify that before the hour the notifications are not sent.
        $plugin->set_config('expirynotifylast', time() - 60 * 60 * 24);
        $plugin->set_config('expirynotifyhour', '24');

        $plugin->send_expiry_notifications($trace);
        $this->assertEquals(0, $sink->count());

        $plugin->set_config('expirynotifyhour', '0');
        $plugin->send_expiry_notifications($trace);
        $this->assertEquals(6, $sink->count());
    }

    public function test_send_waitlist_notifications() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->preventResetByRollback(); // Messaging does not like transactions...

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');
        $trace = new \null_progress_trace();

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'xuser1']);
        $user2 = $this->getDataGenerator()->create_user(['lastname' => 'xuser2']);
        $user3 = $this->getDataGenerator()->create_user(['lastname' => 'xuser3']);
        $user4 = $this->getDataGenerator()->create_user(['lastname' => 'xuser4']);
        $user5 = $this->getDataGenerator()->create_user(['lastname' => 'xuser5']);
        $user6 = $this->getDataGenerator()->create_user(['lastname' => 'xuser6']);
        $user7 = $this->getDataGenerator()->create_user(['lastname' => 'xuser6']);
        $user8 = $this->getDataGenerator()->create_user(['lastname' => 'xuser6']);

        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse1']);
        $course2 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse2']);
        $course3 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse3']);

        $this->assertEquals(3, $DB->count_records('enrol', ['enrol' => 'bycategory']));

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customchar2 = 1; // Enable waiting list.
        $instance1->customint3 = 1; // Max enrolled.
        $instance1->customint6 = 1; // New enrols allowed.
        $instance1->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance1);

        $instance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance2->customchar2 = 1; // Enable waiting list.
        $instance2->customint3 = 1; // Max enrolled.
        $instance2->customint6 = 1; // New enrols allowed.
        $instance2->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance2);

        $now = time();
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user4->id, $now);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user2->id, $now + 1);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user6->id, $now + 2);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user3->id, $now + 3, 5);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user7->id, $now + 4, 4);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user5->id, $now + 5);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user8->id, $now + 6);

        enrol_bycategory_phpunit_util::add_to_waitlist($instance2->id, $user7->id, $now);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance2->id, $user6->id, $now + 1);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance2->id, $user1->id, $now + 2);

        $sink = $this->redirectMessages();

        $plugin->send_waitlist_notifications($trace);
        $messages = $sink->get_messages();
        $this->assertEquals(8, $sink->count());

        $this->assertEquals($user4->id, $messages[0]->useridto);
        $this->assertEquals($user2->id, $messages[1]->useridto);
        $this->assertEquals($user6->id, $messages[2]->useridto);
        $this->assertEquals($user7->id, $messages[3]->useridto);
        $this->assertEquals($user5->id, $messages[4]->useridto);

        $this->assertEquals($user7->id, $messages[5]->useridto);
        $this->assertEquals($user6->id, $messages[6]->useridto);
        $this->assertEquals($user1->id, $messages[7]->useridto);

        // Make sure that notifications are not repeated.
        $sink->clear();

        $plugin->send_waitlist_notifications($trace);
        $messages = $sink->get_messages();
        $this->assertEquals(8, $sink->count());

        $this->assertEquals($user4->id, $messages[0]->useridto);
        $this->assertEquals($user2->id, $messages[1]->useridto);
        $this->assertEquals($user6->id, $messages[2]->useridto);
        $this->assertEquals($user5->id, $messages[3]->useridto);
        $this->assertEquals($user8->id, $messages[4]->useridto);

        $this->assertEquals($user7->id, $messages[5]->useridto);
        $this->assertEquals($user6->id, $messages[6]->useridto);
        $this->assertEquals($user1->id, $messages[7]->useridto);

        $sink->clear();
    }

    public function test_show_enrolme_link() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->preventResetByRollback(); // Messaging does not like transactions...

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $course4 = $this->getDataGenerator()->create_course();
        $course5 = $this->getDataGenerator()->create_course();
        $course6 = $this->getDataGenerator()->create_course();
        $course7 = $this->getDataGenerator()->create_course();
        $course8 = $this->getDataGenerator()->create_course();
        $course9 = $this->getDataGenerator()->create_course();
        $course10 = $this->getDataGenerator()->create_course();
        $course11 = $this->getDataGenerator()->create_course();
        $course12 = $this->getDataGenerator()->create_course();
        $course13 = $this->getDataGenerator()->create_course();

        // New enrolments are allowed and enrolment instance is enabled.
        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $DB->update_record('enrol', $instance1);
        $plugin->update_status($instance1, ENROL_INSTANCE_ENABLED);

        // New enrolments are not allowed, but enrolment instance is enabled.
        $instance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance2->customint6 = 0;
        $DB->update_record('enrol', $instance2);
        $plugin->update_status($instance2, ENROL_INSTANCE_ENABLED);

        // New enrolments are allowed , but enrolment instance is disabled.
        $instance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance3->customint6 = 1;
        $DB->update_record('enrol', $instance3);
        $plugin->update_status($instance3, ENROL_INSTANCE_DISABLED);

        // New enrolments are not allowed and enrolment instance is disabled.
        $instance4 = $DB->get_record('enrol', ['courseid' => $course4->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance4->customint6 = 0;
        $DB->update_record('enrol', $instance4);
        $plugin->update_status($instance4, ENROL_INSTANCE_DISABLED);

        // Course required to pass another course from specific category.
        $category1 = $this->getDataGenerator()->create_category();
        $instance5 = $DB->get_record('enrol', ['courseid' => $course5->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance5->customint1 = $category1->id;
        $DB->update_record('enrol', $instance5);
        $plugin->update_status($instance5, ENROL_INSTANCE_ENABLED);

        // Enrol start date is in future.
        $instance7 = $DB->get_record('enrol', ['courseid' => $course6->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance7->customint6 = 1;
        $instance7->enrolstartdate = time() + 60;
        $DB->update_record('enrol', $instance7);
        $plugin->update_status($instance7, ENROL_INSTANCE_ENABLED);

        // Enrol start date is in past.
        $instance8 = $DB->get_record('enrol', ['courseid' => $course7->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance8->customint6 = 1;
        $instance8->enrolstartdate = time() - 60;
        $DB->update_record('enrol', $instance8);
        $plugin->update_status($instance8, ENROL_INSTANCE_ENABLED);

        // Enrol end date is in future.
        $instance9 = $DB->get_record('enrol', ['courseid' => $course8->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance9->customint6 = 1;
        $instance9->enrolenddate = time() + 60;
        $DB->update_record('enrol', $instance9);
        $plugin->update_status($instance9, ENROL_INSTANCE_ENABLED);

        // Enrol end date is in past.
        $instance10 = $DB->get_record('enrol', ['courseid' => $course9->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance10->customint6 = 1;
        $instance10->enrolenddate = time() - 60;
        $DB->update_record('enrol', $instance10);
        $plugin->update_status($instance10, ENROL_INSTANCE_ENABLED);

        // Maximum enrolments reached, waitlist not enabled.
        $instance11 = $DB->get_record('enrol', ['courseid' => $course10->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance11->customint6 = 1;
        $instance11->customint3 = 1;
        $DB->update_record('enrol', $instance11);
        $plugin->update_status($instance11, ENROL_INSTANCE_ENABLED);
        $plugin->enrol_user($instance11, $user2->id, $studentrole->id);

        // Maximum enrolments not reached.
        $instance12 = $DB->get_record('enrol', ['courseid' => $course11->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance12->customint6 = 1;
        $instance12->customint3 = 1;
        $DB->update_record('enrol', $instance12);
        $plugin->update_status($instance12, ENROL_INSTANCE_ENABLED);

        // Maximum enrolments reached, waitlist enabled.
        $instance13 = $DB->get_record('enrol', ['courseid' => $course12->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance13->customint6 = 1;
        $instance13->customint3 = 1;
        $instance13->customchar2 = 1;
        $DB->update_record('enrol', $instance13);
        $plugin->update_status($instance13, ENROL_INSTANCE_ENABLED);
        $plugin->enrol_user($instance13, $user2->id, $studentrole->id);

        // Empty space in course, but users on waitlist.
        // Maximum enrolments reached, waitlist enabled.
        $instance14 = $DB->get_record('enrol', ['courseid' => $course13->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance14->customint6 = 1;
        $instance14->customint3 = 1;
        $instance14->customchar2 = 1;
        $DB->update_record('enrol', $instance14);
        $plugin->update_status($instance14, ENROL_INSTANCE_ENABLED);
        $plugin->enrol_user($instance14, $user2->id, $studentrole->id);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance14->id, $user1->id, time());
        $plugin->unenrol_user($instance14, $user2->id);

        $this->setUser($user1);
        $this->assertTrue($plugin->show_enrolme_link($instance1));
        $this->assertFalse($plugin->show_enrolme_link($instance2));
        $this->assertFalse($plugin->show_enrolme_link($instance3));
        $this->assertFalse($plugin->show_enrolme_link($instance4));
        $this->assertFalse($plugin->show_enrolme_link($instance7));
        $this->assertTrue($plugin->show_enrolme_link($instance8));
        $this->assertTrue($plugin->show_enrolme_link($instance9));
        $this->assertFalse($plugin->show_enrolme_link($instance10));
        $this->assertFalse($plugin->show_enrolme_link($instance11));
        $this->assertTrue($plugin->show_enrolme_link($instance12));
        $this->assertFalse($plugin->show_enrolme_link($instance13));
        $this->assertFalse($plugin->show_enrolme_link($instance14));

        // User didn't pass course in required category.
        $this->assertFalse($plugin->show_enrolme_link($instance5));

        $finishedcourse = $this->getDataGenerator()->create_course();
        $finishedcourse->category = $category1->id;
        $DB->update_record('course', $finishedcourse);
        $this->getDataGenerator()->enrol_user($user1->id, $finishedcourse->id, $studentrole->id);
        $DB->insert_record('course_completions', [
            'userid' => $user1->id,
            'course' => $finishedcourse->id,
            'timecompleted' => time() - 60 * 60 * 24 * 11, // ... two weeks .
        ]);

        // User passed course in required category.
        $this->assertTrue($plugin->show_enrolme_link($instance5));

        // Accept only users who passed course in required category less than 1 hour ago.
        $instance5->customint5 = 60 * 60;
        $DB->update_record('enrol', $instance5);

        // User passed course in required category, but it was too long time ago.
        $this->assertFalse($plugin->show_enrolme_link($instance5));

        $instance5->customint5 = 60 * 60 * 24 * 11;
        $DB->update_record('enrol', $instance5);

        // User passed course in required category and is within the timelimit.
        $this->assertTrue($plugin->show_enrolme_link($instance5));

        $instance5->enrolstartdate = time() - (86400 * 2);
        $instance5->customint5 = 60 * 60 * 24 * 10;
        $instance5->customchar1 = 1;
        $DB->update_record('enrol', $instance5);
        // User can still enrol, because counting starts from enrolstartdate.
        $this->assertTrue($plugin->show_enrolme_link($instance5));

        $instance5->customchar1 = 0;
        $DB->update_record('enrol', $instance5);
        // User can't enrol, because counting starts from now.
        $this->assertFalse($plugin->show_enrolme_link($instance5));
    }

    /**
     * This will check user enrolment only, rest has been tested in test_show_enrolme_link.
     */
    public function test_can_self_enrol() {
        global $DB, $CFG, $OUTPUT;
        $this->resetAfterTest();
        $this->preventResetByRollback();

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

        $expectederrorstring = get_string('canntenrol', 'enrol_bycategory');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $guest = $DB->get_record('user', ['id' => $CFG->siteguest]);

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->assertNotEmpty($editingteacherrole);

        $course1 = $this->getDataGenerator()->create_course();

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $DB->update_record('enrol', $instance1);
        $plugin->update_status($instance1, ENROL_INSTANCE_ENABLED);
        $plugin->enrol_user($instance1, $user2->id, $editingteacherrole->id);

        $this->setUser($guest);
        $this->assertStringContainsString(get_string('noguestaccess', 'enrol'),
                $plugin->can_self_enrol($instance1, true));

        $this->setUser($user1);
        $this->assertTrue($plugin->can_self_enrol($instance1, true));

        // Active enroled user.
        $this->setUser($user2);
        $plugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $this->setUser($user1);
        $this->assertSame($expectederrorstring, $plugin->can_self_enrol($instance1, true));

        // Active enroled user can't enrol again via another enrolment method.
        $instance2id = $plugin->add_instance($course1, ['customint6' => 1]);
        $instance2 = $DB->get_record('enrol', ['id' => $instance2id], '*', MUST_EXIST);
        $canenrol = $plugin->can_self_enrol($instance2, true);
        $this->assertSame($expectederrorstring, $canenrol);
    }

    /**
     * Test get_welcome_email_contact().
     */
    public function test_get_welcome_email_contact() {
        global $DB;
        self::resetAfterTest(true);

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'Marsh']);
        $user2 = $this->getDataGenerator()->create_user(['lastname' => 'Victoria']);
        $user3 = $this->getDataGenerator()->create_user(['lastname' => 'Burch']);
        $user4 = $this->getDataGenerator()->create_user(['lastname' => 'Cartman']);
        $noreplyuser = \core_user::get_noreply_user();

        $course1 = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course1->id);

        // Get editing teacher role.
        $editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->assertNotEmpty($editingteacherrole);

        // Enable self enrolment plugin and set to send email from course contact.
        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');
        $instance1 = enrol_bycategory_phpunit_util::add_enrol_instance($plugin, $course1);
        $instance1->customint6 = 1;
        $instance1->customint4 = ENROL_SEND_EMAIL_FROM_COURSE_CONTACT;
        $DB->update_record('enrol', $instance1);
        $plugin->update_status($instance1, ENROL_INSTANCE_ENABLED);

        // We do not have a teacher enrolled at this point, so it should send as no reply user.
        $contact = $plugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_COURSE_CONTACT, $context);
        $this->assertEquals($noreplyuser, $contact);

        // By default, course contact is assigned to teacher role.
        // Enrol a teacher, now it should send emails from teacher email's address.
        $plugin->enrol_user($instance1, $user1->id, $editingteacherrole->id);

        // We should get the teacher email.
        $contact = $plugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_COURSE_CONTACT, $context);
        $this->assertEquals($user1->username, $contact->username);
        $this->assertEquals($user1->email, $contact->email);

        // Now let's enrol another teacher.
        $plugin->enrol_user($instance1, $user2->id, $editingteacherrole->id);
        $contact = $plugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_COURSE_CONTACT, $context);
        $this->assertEquals($user1->username, $contact->username);
        $this->assertEquals($user1->email, $contact->email);

        $instance1->customint4 = ENROL_SEND_EMAIL_FROM_NOREPLY;
        $DB->update_record('enrol', $instance1);

        $contact = $plugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_NOREPLY, $context);
        $this->assertEquals($noreplyuser, $contact);
    }

    /**
     * Test for getting user enrolment actions.
     */
    public function test_get_user_enrolment_actions() {
        global $CFG, $DB, $PAGE;
        $this->resetAfterTest();

        // Set page URL to prevent debugging messages.
        $PAGE->set_url('/enrol/editinstance.php');

        $pluginname = 'bycategory';

        // Only enable the bycategory enrol plugin.
        $CFG->enrol_plugins_enabled = $pluginname;

        $generator = $this->getDataGenerator();

        // Get the enrol plugin.
        $plugin = enrol_get_plugin($pluginname);

        // Create a course.
        $course = $generator->create_course();

        // Create a teacher.
        $teacher = $generator->create_user();
        // Enrol the teacher to the course.
        $enrolresult = $generator->enrol_user($teacher->id, $course->id, 'editingteacher', $pluginname);
        $this->assertTrue($enrolresult);
        // Create a student.
        $student = $generator->create_user();
        // Enrol the student to the course.
        $enrolresult = $generator->enrol_user($student->id, $course->id, 'student', $pluginname);
        $this->assertTrue($enrolresult);

        // Login as the teacher.
        $this->setUser($teacher);
        require_once($CFG->dirroot . '/enrol/locallib.php');
        $manager = new \course_enrolment_manager($PAGE, $course);
        $userenrolments = $manager->get_user_enrolments($student->id);
        $this->assertCount(1, $userenrolments);

        $ue = reset($userenrolments);
        $actions = $plugin->get_user_enrolment_actions($manager, $ue);
        // ... bycategory enrol has 2 enrol actions -- edit and unenrol.
        $this->assertCount(2, $actions);
    }

    /**
     * Test for making users automatically join a group
     */
    public function test_autojoin_group() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);

        $course1 = $this->getDataGenerator()->create_course();
        $group1data = (object) [
            'courseid' => $course1->id,
            'name' => 'group1',
        ];
        $group1 = $this->getDataGenerator()->create_group($group1data);

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $instance1->customint7 = $group1->id;

        $this->assertFalse(groups_is_member($group1->id, $user1->id));

        $DB->update_record('enrol', $instance1);
        $plugin->update_status($instance1, ENROL_INSTANCE_ENABLED);
        $plugin->enrol_user_manually($instance1, $user1->id);

        $this->assertTrue(groups_is_member($group1->id, $user1->id));
    }

    /**
     * Test Users should not be able to join multiple waitlists on the same course
     */
    public function test_waitlist_multi_join() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);

        $course1 = $this->getDataGenerator()->create_course();

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $instance1->customint3 = 1;
        $instance1->customchar2 = 1;
        $instance1->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance1);

        $instance2id = $plugin->add_instance($course1, [
            'customint6' => 1,
            'customint3' => 1,
            'customchar2' => 1,
            'status' => ENROL_INSTANCE_ENABLED,
        ]);
        $instance2 = $DB->get_record('enrol', ['id' => $instance2id], '*', MUST_EXIST);

        $plugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $plugin->enrol_user($instance2, $user2->id, $studentrole->id);
        $this->setUser($user3);

        enrol_bycategory_phpunit_util::add_to_waitlist($instance2->id, $user3->id, time());

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('redirect');
        $plugin->enrol_page_hook($instance1);
    }
}
