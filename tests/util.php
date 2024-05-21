<?php
// phpcs:disable moodle.PHPUnit.TestCaseNames.Missing
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
    /**
     * Call a non-public method
     * @param object $obj
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function call_method($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Enable the bycategory plugin
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
     * Disable the bycategory plugin
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

     /**
      * Add enrol instance to course
      * @see https://moodle.org/mod/forum/discuss.php?d=318186#p1275913
      * @author 2015 Darko Miletić
      * @param object $plugin
      * @param object $course
      * @return mixed|null
      * @throws coding_exception
      */
    public static function add_enrol_instance($plugin, $course) {
        global $DB;
        $inst = null;
        $pluginname = $plugin->get_name();
        if (!empty($plugin)) {
            $inst = null;
            $instances = enrol_get_instances($course->id, false);
            foreach ($instances as $instance) {
                if ($instance->enrol == $pluginname) {
                    $inst = $instance;
                    break;
                }
            }
            if ($inst === null) {
                $instid = $plugin->add_default_instance($course);
                if ($instid === null) {
                    $instid = $plugin->add_instance($course);
                }
                $inst = $DB->get_record('enrol', ['id' => $instid]);
            }
        }
        return $inst;
    }
}
