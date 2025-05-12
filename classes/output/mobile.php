<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * Enrol bycategory
 *
 * @package   enrol_bycategory
 * @copyright 2025 Andreas Rosenthal, ssystems GmbH <arosenthal@ssystems.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_bycategory\output;


/**
 * Mobile output class for enrol_bycategory
 *
 * @package   enrol_bycategory
 * @copyright 2025 Andreas Rosenthal, ssystems GmbH <arosenthal@ssystems.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {


    /**
     * Returns the JS to implement app support for enrol self test.
     *
     * @param array $args Arguments from tool_mobile_get_content WS
     *
     * @return array   HTML, javascript and otherdata
     */
    public static function mobile_js($args) {
        global $CFG;
        return [
            'templates' => [],
            'javascript' => file_get_contents($CFG->dirroot . '/enrol/bycategory/mobileapp/mobile.js'),
            'otherdata' => '',
            'files' => '',
        ];
    }

}
