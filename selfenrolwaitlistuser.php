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
 * Enrol a user via Notification with token
 *
 * @package    enrol_bycategory
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version3\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Rules\ValidAt;
use ParagonIE\Paseto\Purpose;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/vendor/autoload.php');

defined('MOODLE_INTERNAL') || die();

require_login();
$token = required_param('token', PARAM_TEXT);
$secret = get_config('enrol_bycategory', 'secret');
$dashboardurl = new moodle_url('/my');
if($secret == false) {
    redirect($dashboardurl, get_string('secretmissing', 'enrol_bycategory'), null, notification::NOTIFY_ERROR);
}

$parser = create_parser($secret);

$tokendata = null;
try {
    $tokendata = $parser->parse($token);
} catch (PasetoException $ex) {
    redirect($dashboardurl, get_string('tokeninvalid', 'enrol_bycategory'), null, notification::NOTIFY_ERROR, true);
}

$instanceid = $tokendata->get('instanceid');
$waitlisturl = new moodle_url('/enrol/bycategory/waitlist.php', ['enrolid' => $instanceid]);

$waitlist = new enrol_bycategory_waitlist($instanceid);
$userid = $tokendata->get('userid');

// Token is not for the current user.
if ($userid !== $USER->id) {
    redirect($waitlisturl, get_string('wrongtokenuser', 'enrol_bycategory'), null, notification::NOTIFY_WARNING);
}

if ($waitlist->is_on_waitlist($userid) === false) {
    redirect($waitlisturl, get_string('usernotonwaitlist', 'enrol_bycategory'), null, notification::NOTIFY_INFO);
}

$instance = $DB->get_record('enrol', array('id' => $instanceid, 'enrol' => 'bycategory'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$context = context_course::instance($course->id, MUST_EXIST);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

$PAGE->set_url('/enrol/bycategory/selfenrolwaitlistuser.php', ['token' => $token]);

$canenrol = $waitlist->can_enrol($instance, $userid, true);
// Sorry you missed your chance, try again next time
if($canenrol !== true) {
    $waitlist->reset_notification_counter($user->id);
    redirect($waitlisturl, get_string('enrolchancemissed', 'enrol_bycategory'), null, notification::NOTIFY_INFO);
}

$enrolmethod = 'bycategory';
/** @var enrol_bycategory_plugin */
$enrol = enrol_get_plugin($enrolmethod);
if($enrol === null) {
    redirect($waitlisturl, get_string('enrolmentmissing', 'enrol_bycategory'), null, notification::NOTIFY_ERROR);
}

$instances = enrol_get_instances($course->id, true);
$bycategoryinstance = null;
foreach ($instances as $instance) {
    if ($instance->enrol == $enrolmethod) {
        $bycategoryinstance = $instance;
        break;
    }
}

if($bycategoryinstance === null) {
    redirect($waitlisturl, get_string('enrolmentmissing', 'enrol_bycategory'), null, notification::NOTIFY_ERROR);
}

$enrolresult = $enrol->enrol_user_manually($bycategoryinstance, $user->id);
if($enrolresult === true) {
    $waitlist->remove_user($user->id);
}

redirect($courseurl, get_string('youenrolledincourse'), null, notification::NOTIFY_SUCCESS);

function create_parser($secret)
{
    $sharedkey = new SymmetricKey($secret);
    $parser = (new Parser())
        ->setKey($sharedkey)
        ->addRule(new ValidAt())
        ->setPurpose(Purpose::local())
        ->setAllowedVersions(ProtocolCollection::v3());

    return $parser;
}
