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
 * bycategory enrolment privacy tests.
 *
 * @package    enrol_bycategory
 * @category   test
 * @copyright  2023 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 *             based on work by 2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_bycategory\privacy;

use stdClass;
use context_course;
use context_user;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use enrol_bycategory\privacy\provider;
use enrol_bycategory_phpunit_util;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../util.php');

global $CFG;

 /**
  * Testcasse for privacy provider
  * @covers \enrol_bycategory\privacy\provider
  */
class provider_test extends provider_testcase {

    /** @var stdClass */
    private $user1 = null;
    /** @var stdClass */
    private $user2 = null;
    /** @var stdClass */
    private $user3 = null;

    /** @var stdClass */
    private $course1 = null;
    /** @var stdClass */
    private $course2 = null;

    /** @var context_course */
    private $coursecontext1 = null;
    /** @var context_course */
    private $coursecontext2 = null;

    /** @var stdClass */
    private $instance1 = null;
    /** @var stdClass */
    private $instance2 = null;

    public function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_get_metadata() {
        $collection = new collection('enrol_bycategory');
        $collection = provider::get_metadata($collection);

        $this->assertNotEmpty($collection);
    }

    public function test_get_contexts_for_user() {
        $this->create_course_setup();

        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(2, $contextlist->count());
        $contextids = $contextlist->get_contextids();
        $this->assertContainsEquals($this->coursecontext1->id, $contextids);
        $this->assertContainsEquals($this->coursecontext2->id, $contextids);

        $contextlist = provider::get_contexts_for_userid($this->user2->id);
        $this->assertEquals(1, $contextlist->count());
        $contextids = $contextlist->get_contextids();
        $this->assertContainsEquals($this->coursecontext1->id, $contextids);

        $contextlist = provider::get_contexts_for_userid($this->user3->id);
        $this->assertEquals(1, $contextlist->count());
        $contextids = $contextlist->get_contextids();
        $this->assertContainsEquals($this->coursecontext1->id, $contextids);
    }

    public function test_get_users_in_context() {
        $this->create_course_setup();

        $userlist = new userlist($this->coursecontext1, 'enrol_bycategory');
        provider::get_users_in_context($userlist);
        $this->assertEqualsCanonicalizing(
            [$this->user1->id, $this->user2->id, $this->user3->id],
            $userlist->get_userids()
        );

        $userlist = new userlist($this->coursecontext2, 'enrol_bycategory');
        provider::get_users_in_context($userlist);
        $this->assertEqualsCanonicalizing(
            [$this->user1->id],
            $userlist->get_userids()
        );
    }

    public function test_export_user_data() {
        $this->create_course_setup();

        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(2, $contextlist->count());

        $approvedcontextlist = new approved_contextlist(
            $this->user1,
            'enrol_bycategory',
            $contextlist->get_contextids()
        );

        provider::export_user_data($approvedcontextlist);
        $subcontext = \core_enrol\privacy\provider::get_subcontext([get_string('pluginname', 'enrol_bycategory')]);

        $writer = writer::with_context($this->coursecontext1);
        $this->assertNotEmpty($writer->get_data($subcontext));

        $writer = writer::with_context($this->coursecontext2);
        $this->assertNotEmpty($writer->get_data($subcontext));
    }

    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $this->create_course_setup();
        $this->assertEquals(3, $DB->count_records('enrol_bycategory_waitlist', ['instanceid' => $this->instance1->id]));
        provider::delete_data_for_all_users_in_context($this->coursecontext1);
        $this->assertEquals(0, $DB->count_records('enrol_bycategory_waitlist', ['instanceid' => $this->instance1->id]));

        $this->assertEquals(1, $DB->count_records('enrol_bycategory_waitlist', ['instanceid' => $this->instance2->id]));
        provider::delete_data_for_all_users_in_context($this->coursecontext2);
        $this->assertEquals(0, $DB->count_records('enrol_bycategory_waitlist', ['instanceid' => $this->instance2->id]));
    }

    public function test_delete_data_for_user() {
        global $DB;

        $this->create_course_setup();

        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(2, $contextlist->count());
        $contextids = $contextlist->get_contextids();
        $this->assertContainsEquals($this->coursecontext1->id, $contextids);
        $this->assertContainsEquals($this->coursecontext2->id, $contextids);

        $approvedcontextlist = new approved_contextlist(
            $this->user1,
            'enrol_bycategory',
            $contextids
        );

        provider::delete_data_for_user($approvedcontextlist);
        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(0, $contextlist->count());
        $this->assertEquals(0, $DB->count_records('enrol_bycategory_waitlist', ['userid' => $this->user1->id]));
    }

    public function test_delete_data_for_users() {
        global $DB;

        $this->create_course_setup();

        $userlist = new \core_privacy\local\request\userlist($this->coursecontext1, 'enrol_bycategory');
        provider::get_users_in_context($userlist);
        $this->assertEqualsCanonicalizing(
            [$this->user1->id, $this->user2->id, $this->user3->id],
            $userlist->get_userids()
        );

        $approveduserlist = new \core_privacy\local\request\approved_userlist($this->coursecontext1, 'enrol_bycategory', [
            $this->user1->id,
            $this->user3->id,
        ]);

        provider::delete_data_for_users($approveduserlist);
        $userlist = new \core_privacy\local\request\userlist($this->coursecontext1, 'enrol_bycategory');
        provider::get_users_in_context($userlist);
        $this->assertEquals([$this->user2->id], $userlist->get_userids());
    }

    /**
     * Helper function to setup course and add users to the waiting list
     */
    protected function create_course_setup() {
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $this->coursecontext1 = context_course::instance($course1->id);
        $this->coursecontext2 = context_course::instance($course2->id);

        enrol_bycategory_phpunit_util::enable_plugin();
        $plugin = enrol_get_plugin('bycategory');

        $user1 = $this->user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->user3 = $this->getDataGenerator()->create_user();

        $instance1 = $this->instance1 = enrol_bycategory_phpunit_util::add_enrol_instance($plugin, $course1);
        $this->setup_enrol($instance1);

        $instance2 = $this->instance2 = enrol_bycategory_phpunit_util::add_enrol_instance($plugin, $course2);
        $this->setup_enrol($instance2);

        $now = time();
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user1->id, $now);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user2->id, $now);
        enrol_bycategory_phpunit_util::add_to_waitlist($instance1->id, $user3->id, $now);

        enrol_bycategory_phpunit_util::add_to_waitlist($instance2->id, $user1->id, $now);
    }

    protected function setup_enrol($instance) {
        global $DB;

        $instance->customchar2 = 1; // Enable waiting list.
        $instance->customint3 = 1; // Max enrolled.
        $instance->customint6 = 1; // New enrols allowed.
        $instance->status = ENROL_INSTANCE_ENABLED;

        $DB->update_record('enrol', $instance);
    }
}
