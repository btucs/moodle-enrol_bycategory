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
 * Upgrade handling for bycategory enrolment plugin.
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function
 * @param number $oldversion
 * @return bool
 */
function xmldb_enrol_bycategory_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022060205) {

        // Define table enrol_bycategory_waitlist to be created.
        $table = new xmldb_table('enrol_bycategory_waitlist');

        // Adding fields to table enrol_bycategory_waitlist.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table enrol_bycategory_waitlist.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('instanceid', XMLDB_KEY_FOREIGN, ['instanceid'], 'enrol', ['id']);

        // Conditionally launch create table for enrol_bycategory_waitlist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Bycategory savepoint reached.
        upgrade_plugin_savepoint(true, 2022060205, 'enrol', 'bycategory');
    }

    if ($oldversion < 2022060206) {

        // Define field notified to be added to enrol_bycategory_waitlist.
        $table = new xmldb_table('enrol_bycategory_waitlist');
        $field = new xmldb_field('notified', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field notified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Bycategory savepoint reached.
        upgrade_plugin_savepoint(true, 2022060206, 'enrol', 'bycategory');
    }

    if ($oldversion < 2022060210) {

        // Define table enrol_bycategory_token to be created.
        $table = new xmldb_table('enrol_bycategory_token');

        // Adding fields to table enrol_bycategory_token.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('token', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('waitlistid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table enrol_bycategory_token.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $table->add_key('waitlistid', XMLDB_KEY_FOREIGN, ['waitlistid'], 'enrol_bycategory_waitlist', ['id']);

        // Adding indexes to table enrol_bycategory_token.
        $table->add_index('token', XMLDB_INDEX_UNIQUE, ['token']);

        // Conditionally launch create table for enrol_bycategory_token.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Bycategory savepoint reached.
        upgrade_plugin_savepoint(true, 2022060210, 'enrol', 'bycategory');
    }

    if ($oldversion < 2023051200) {

        $sql = "UPDATE {enrol}
                SET customchar1 = CAST(customint7 AS VARCHAR(255)), customint7 = NULL,
                    customchar2 = CAST(customint8 AS VARCHAR(255)), customint8 = NULL
                WHERE enrol = 'bycategory'"
        ;

        $DB->execute($sql);

        // Bycategory savepoint reached.
        upgrade_plugin_savepoint(true, 2023051200, 'enrol', 'bycategory');
    }

    return true;
}
