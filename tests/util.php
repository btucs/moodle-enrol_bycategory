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
 * phpunit utility class
 *
 * @package     enrol_bycategory
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_bycategory_phpunit_util {
    public static function call_method($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * @author  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
     *          based on work by 2010 Eugene Venter enrol_paypal
     */
    public static function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['bycategory'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * @author  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
     *          based on work by 2010 Eugene Venter enrol_paypal
     */
    public static function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['bycategory']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * add a user to the waiting list
     * @param int $instanceid enrol instance id
     * @param int $userid user id
     * @param int $time time created
     * @param int $notified amount of sent notifications
     * @return int created entry id
     */
    public static function add_to_waitlist($instanceid, $userid, $time, $notified = 0) {
        global $DB;

        return $DB->insert_record('enrol_bycategory_waitlist', [
            'userid' => $userid,
            'instanceid' => $instanceid,
            'usermodified' => $userid,
            'timecreated' => $time,
            'timemodified' => $time,
            'notified' => $notified,
        ], true, false);
    }
}
