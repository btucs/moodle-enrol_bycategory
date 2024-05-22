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
 * Privacy Subsystem implementation for enrol_bycategory.
 *
 * @package     enrol_bycategory
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Creates an action icon with a badge to inform about amount
  */
class enrol_bycategory_badge_action_icon {

    /**
     * Renders an action_icon with notification amount.
     *
     * This function uses the {@see core_renderer::action_link()} method for the
     * most part. What it does different is prepare the icon as HTML and use it
     * as the link text.
     *
     * Theme developers: If you want to change how action links and/or icons are rendered,
     * consider overriding function {@see core_renderer::render_action_link()} and
     * {@see core_renderer::render_pix_icon()}.
     *
     * @param string|moodle_url $url A string URL or moodel_url
     * @param pix_icon $pixicon
     * @param int $amount
     * @param component_action $action
     * @param array $attributes associative array of html link attributes + disabled
     * @param bool $linktext show title next to image in link
     * @return string HTML fragment
     */
    public function badge_action_icon(
        $url,
        pix_icon $pixicon,
        $amount,
        component_action $action = null,
        array $attributes = null,
        $linktext = false
    ) {
        global $OUTPUT;

        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $attributes = (array)$attributes;

        if (empty($attributes['class'])) {
            // Let ppl override the class via $options.
            $attributes['class'] = 'action-icon badge-action-icon';
        }

        $icon = $OUTPUT->render($pixicon);

        if ($linktext) {
            $text = $pixicon->attributes['alt'];
        } else {
            $text = '';
        }

        $link = new action_link($url, $text.$icon, $action, $attributes);
        $badgedata = $link->export_for_template($OUTPUT);
        $badgedata->amount = $amount;

        $result = $OUTPUT->render_from_template('enrol_bycategory/badge_action_icon', $badgedata);

        return $result;
    }
}
