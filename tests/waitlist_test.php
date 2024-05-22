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

use enrol_bycategory_waitlist;
use enrol_bycategory_phpunit_util;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/enrol/bycategory/lib.php');

/**
 * Testcase for enrol_bycategory_waitlist
 * @covers \enrol_bycategory_waitlist
 */
class waitlist_test extends \advanced_testcase {

    /** @var string */
    private $tablename = 'enrol_bycategory_waitlist';
    /** @var int */
    private $instanceid = 1;

    public function test_get_count() {
        global $DB;

        $this->resetAfterTest();

        $waitlist = new enrol_bycategory_waitlist($this->instanceid);
        $this->assertEquals($waitlist->get_count(), 0);

        $userid = 1;
        $now = time();
        $DB->insert_record($this->tablename, [
            'userid' => $userid,
            'instanceid' => $this->instanceid,
            'usermodified' => $userid,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $this->assertEquals($waitlist->get_count(), 1);

        $DB->insert_record($this->tablename, [
            'userid' => $userid,
            'instanceid' => 2,
            'usermodified' => $userid,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $this->assertEquals($waitlist->get_count(), 1);
    }

    public function test_add_user() {
        global $DB;

        $this->resetAfterTest();
        $waitlist = new enrol_bycategory_waitlist($this->instanceid);

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);
        $this->assertEquals($count, 0);

        $userid = 1;
        $waitlist->add_user($userid);

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);
        $this->assertEquals($count, 1);

        $exits = $DB->record_exists($this->tablename, [
            'instanceid' => $this->instanceid,
            'userid' => $userid,
        ]);
        $this->assertTrue($exits);
    }

    public function test_remove_user() {
        global $DB;

        $this->resetAfterTest();
        $waitlist = new enrol_bycategory_waitlist($this->instanceid);
        $userid = 1;
        $now = time();
        $DB->insert_record($this->tablename, [
            'userid' => $userid,
            'instanceid' => $this->instanceid,
            'usermodified' => $userid,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $user2id = 2;
        $DB->insert_record($this->tablename, [
            'userid' => $user2id,
            'instanceid' => $this->instanceid,
            'usermodified' => $user2id,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);
        $this->assertEquals($count, 2);

        $waitlist->remove_user($userid);

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);
        $this->assertEquals($count, 1);
    }

    public function test_remove_users() {
        global $DB;

        $this->resetAfterTest();
        $waitlist = new enrol_bycategory_waitlist($this->instanceid);
        $userid = 1;
        $now = time();
        $DB->insert_record($this->tablename, [
            'userid' => $userid,
            'instanceid' => $this->instanceid,
            'usermodified' => $userid,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $user2id = 2;
        $DB->insert_record($this->tablename, [
            'userid' => $user2id,
            'instanceid' => $this->instanceid,
            'usermodified' => $user2id,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $user3id = 3;
        $DB->insert_record($this->tablename, [
            'userid' => $user3id,
            'instanceid' => $this->instanceid,
            'usermodified' => $user3id,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);
        $this->assertEquals($count, 3);

        $waitlist->remove_users([$userid, $user3id]);

        $count = $DB->count_records($this->tablename, ['instanceid' => $this->instanceid]);
        $this->assertEquals($count, 1);

        $user2exists = $DB->record_exists($this->tablename, ['userid' => $user2id, 'instanceid' => $this->instanceid]);
        $this->assertTrue($user2exists);
    }

    public function test_is_on_waitlist() {
        global $DB;

        $this->resetAfterTest();
        $waitlist = new enrol_bycategory_waitlist($this->instanceid);
        $userid = 1;
        $now = time();

        $isonwaitlist = $waitlist->is_on_waitlist($userid);
        $this->assertFalse($isonwaitlist);

        $DB->insert_record($this->tablename, [
            'userid' => $userid,
            'instanceid' => $this->instanceid,
            'usermodified' => $userid,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $isonwaitlist = $waitlist->is_on_waitlist($userid);
        $this->assertTrue($isonwaitlist);
    }

    public function test_is_on_waitlist_bulk() {
        global $DB;

        $this->resetAfterTest();
        $now = time();

        $useridsonwaitlist = [101, 102, 103];
        $otheruserids = [104, 105];

        foreach ($useridsonwaitlist as $userid) {
            $DB->insert_record($this->tablename, [
                'userid' => $userid,
                'instanceid' => $this->instanceid,
                'usermodified' => $userid,
                'timecreated' => $now,
                'timemodified' => $now,
            ], false, false);
        }

        foreach ($otheruserids as $userid) {
            $DB->insert_record($this->tablename, [
                'userid' => $userid,
                'instanceid' => $this->instanceid + 1,
                'usermodified' => $userid,
                'timecreated' => $now,
                'timemodified' => $now,
            ], false, false);
        }

        $waitlist = new enrol_bycategory_waitlist(($this->instanceid));
        $result = $waitlist->is_on_waitlist_bulk(array_merge($useridsonwaitlist, $otheruserids));

        $this->assertEquals($useridsonwaitlist, $result['onwaitlist']);
        $this->assertEquals($otheruserids, $result['missing']);
    }

    public function test_get_user_position() {
        global $DB;

        $this->resetAfterTest();
        $waitlist = new enrol_bycategory_waitlist($this->instanceid);
        $user1id = 101;
        $user2id = 102;
        $user3id = 103;
        $now = time();

        $DB->insert_record($this->tablename, [
            'userid' => $user1id,
            'instanceid' => $this->instanceid,
            'usermodified' => $user1id,
            'timecreated' => $now,
            'timemodified' => $now,
        ], false, false);

        $DB->insert_record($this->tablename, [
            'userid' => $user2id,
            'instanceid' => $this->instanceid,
            'usermodified' => $user2id,
            'timecreated' => $now + 1,
            'timemodified' => $now + 1,
        ], false, false);

        $DB->insert_record($this->tablename, [
            'userid' => $user3id,
            'instanceid' => $this->instanceid,
            'usermodified' => $user3id,
            'timecreated' => $now + 2,
            'timemodified' => $now + 2,
        ], false, false);

        $this->assertEquals($waitlist->get_user_position($user1id), 1);
        $this->assertEquals($waitlist->get_user_position($user2id), 2);
        $this->assertEquals($waitlist->get_user_position($user3id), 3);
        $this->assertEquals($waitlist->get_user_position(104), -1);

        $DB->set_field($this->tablename, 'notified', 5, ['userid' => $user1id, 'instanceid' => $this->instanceid]);
        $this->assertEquals($waitlist->get_user_position($user2id), 1);
    }

    public function test_can_enrol() {
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
        $this->assertStringContainsString(
            get_string('noguestaccess', 'enrol'),
            $plugin->can_self_enrol($instance1, true)
        );

        $this->setUser($user1);
        $this->assertTrue($plugin->can_self_enrol($instance1, true));

        // Active enroled user.
        $this->setUser($user2);
        $plugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $this->setUser($user1);
        $this->assertSame($expectederrorstring, $plugin->can_self_enrol($instance1, true));
    }

    public function test_select_courses_with_available_space() {

        global $DB;
        $this->resetAfterTest();

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'xuser1']);

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

        $instance3 = $DB->get_record('enrol', ['courseid' => $course3->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance3->customchar2 = 1; // Enable waiting list.
        $instance3->customint3 = 1; // Max enrolled.
        $instance3->customint6 = 1; // New enrols allowed.
        $instance3->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance3);

        $result = enrol_bycategory_waitlist::select_courses_with_available_space();
        $this->assertEquals(3, count($result));

        // When the course is full it should not show up.
        $plugin->enrol_user($instance3, $user1->id);
        $result = enrol_bycategory_waitlist::select_courses_with_available_space();
        $this->assertEquals(2, count($result));

        // If enrols are disabled it should not show up.
        $instance2->customint6 = 0;
        $DB->update_record('enrol', $instance2);
        $result = enrol_bycategory_waitlist::select_courses_with_available_space();
        $this->assertEquals(1, count($result));

        // If enrol is disabled it should not show up.
        $instance1->status = ENROL_INSTANCE_DISABLED;
        $DB->update_record('enrol', $instance1);
        $result = enrol_bycategory_waitlist::select_courses_with_available_space();
        $this->assertEquals(0, count($result));
    }

    public function test_select_users_from_waitlist_for_notification() {
        global $DB, $CFG;
        $this->resetAfterTest();

        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

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

        $result = enrol_bycategory_waitlist::select_users_from_waitlist_for_notification([$instance1->id, $instance2->id]);
        $this->assertEquals(8, count($result));

        $waitlistusers = array_values($result);
        $this->assertEquals($waitlistusers[0]->userid, $user4->id);
        $this->assertEquals($waitlistusers[1]->userid, $user2->id);
        $this->assertEquals($waitlistusers[2]->userid, $user6->id);
        $this->assertEquals($waitlistusers[3]->userid, $user7->id);
        $this->assertEquals($waitlistusers[4]->userid, $user5->id);

        $this->assertEquals($waitlistusers[0]->instanceid, $instance1->id);
        $this->assertEquals($waitlistusers[1]->instanceid, $instance1->id);
        $this->assertEquals($waitlistusers[2]->instanceid, $instance1->id);
        $this->assertEquals($waitlistusers[3]->instanceid, $instance1->id);
        $this->assertEquals($waitlistusers[4]->instanceid, $instance1->id);

        $this->assertEquals($waitlistusers[5]->userid, $user7->id);
        $this->assertEquals($waitlistusers[6]->userid, $user6->id);
        $this->assertEquals($waitlistusers[7]->userid, $user1->id);

        $this->assertEquals($waitlistusers[5]->instanceid, $instance2->id);
        $this->assertEquals($waitlistusers[6]->instanceid, $instance2->id);
        $this->assertEquals($waitlistusers[7]->instanceid, $instance2->id);
    }

    public function test_increase_notified() {
        global $DB, $CFG;
        $this->resetAfterTest();
        enrol_bycategory_phpunit_util::enable_plugin();

        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'xuser1']);
        $user2 = $this->getDataGenerator()->create_user(['lastname' => 'xuser2']);

        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse1']);

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customchar2 = 1; // Enable waiting list.
        $instance1->customint3 = 1; // Max enrolled.
        $instance1->customint6 = 1; // New enrols allowed.
        $instance1->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance1);

        $now = time();
        $waitlist1id = enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user1->id, $now);
        $waitlist2id = enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user2->id, $now, 3);

        $result = enrol_bycategory_waitlist::increase_notified([$waitlist1id, $waitlist2id]);

        $notified1 = $DB->get_field('enrol_bycategory_waitlist', 'notified', ['id' => $waitlist1id]);
        $this->assertEquals(1, $notified1);
        $notified2 = $DB->get_field('enrol_bycategory_waitlist', 'notified', ['id' => $waitlist2id]);
        $this->assertEquals(4, $notified2);
    }

    public function test_reset_notification_counter() {

        global $DB, $CFG;
        $this->resetAfterTest();
        enrol_bycategory_phpunit_util::enable_plugin();
        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'xuser1']);
        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'xcourse1']);

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'bycategory'], '*', MUST_EXIST);
        $instance1->customchar2 = 1; // Enable waiting list.
        $instance1->customint3 = 1; // Max enrolled.
        $instance1->customint6 = 1; // New enrols allowed.
        $instance1->status = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance1);

        $now = time();
        $waitlist1id = enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user1->id, $now);
        $DB->set_field('enrol_bycategory_waitlist', 'notified', 3, ['id' => $waitlist1id]);
        $notified1 = $DB->get_field('enrol_bycategory_waitlist', 'notified', ['id' => $waitlist1id]);

        $this->assertEquals(3, $notified1);

        $waitlist = new enrol_bycategory_waitlist(($instance1->id));
        $waitlist->reset_notification_counter($user1->id);
        $notified1 = $DB->get_field('enrol_bycategory_waitlist', 'notified', ['id' => $waitlist1id]);

        $this->assertEquals(0, $notified1);
    }
}
