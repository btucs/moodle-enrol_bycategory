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
 * Plugin strings are defined here.
 *
 * @package     enrol_bycategory
 * @category    string
 * @copyright   2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autogroup'] = 'Teilnehmer*innen automatisch in ausgewählte Gruppe einschreiben';
$string['autogroup_help'] = 'Teilnehmer*innen werden bei Einschreibung über diese Einschreibemethode automatisch in die ausgewählte Gruppe eingeschrieben.';
$string['bulkenrol'] = 'Ausgewählte Einschrieben in';
$string['bulkenrolconfirmmessage'] = 'Sind sie sicher, dass sie die folgenden Teilnehmer*innen:

{$a->users}

in Kurs {$a->coursename} über Einschreibemethode {$a->enrol} einschrieben möchten?

Sie werden anschließend von dieser Warteliste entfernt.';
$string['bulkenrolconfirmtitle'] = 'Auswahl bestätigen';
$string['bulkenrolsuccess'] = 'Die ausgewählten Personen wurden erfolgreich eingeschrieben';
$string['bulkenrolusersmissing'] = 'Die folgenden Personen konnten nicht eingeschrieben werden, da sie nicht Teil der Warteliste waren:

{$a}

Alle anderen Personen wurden erfolgreich eingeschrieben.';
$string['canntenrol'] = 'Einschreibung deaktiviert oder inaktiv';
$string['canntenrolearly'] = 'Sie können sich noch nicht einschreiben, weil die Einschreibung erst am {$a} beginnt.';
$string['canntenrollate'] = 'Sie können sich nicht mehr einschreiben, weil die Einschreibung am {$a} beendet wurde.';
$string['category'] = 'abgeschlossener Kurs in Kursbereich';
$string['category_help'] = 'Wählen Sie einen Kursbereich aus in dem ein Kurs abgeschlossen sein muss, um Zugang zu diesem Kurs zu erhalten.

Wenn Sie "Keine Einschränkung" auswählen kann sich jeder Einschreiben.';
$string['confirmbulkdeleteenrolment'] = 'Möchten Sie die Einschreibungen dieser Nutzer/innen wirklich löschen?';
$string['customwelcomemessage'] = 'Begrüßungstext';
$string['customwelcomemessage_help'] = 'Ein Begrüßungstext kann ein einfacher Text sein oder im Moodle-Auto-Format auch HTML-Tags und MultiLang-Tags enthalten.

Sie können folgende Platzhalter im Text verwenden:

* Kursname {$a->coursename}
* Link zum Nutzerprofil {$a->profileurl}
* Nutzer-E-Mail-Adresse {$a->email}
* Vollständiger Nutzername {$a->fullname}';
$string['completionperiod'] = 'Zeitraum seit Abschluss';
$string['completionperiod_help'] = 'Der erlaubte maximale Zeitraum seit Abschluss eines Kurses aus dem eingestellten Kursbereich.';
$string['defaultrole'] = 'Rolle im Kurs';
$string['defaultrole_desc'] = 'Wählen Sie eine Rolle aus, die Nutzer/innen bei der Selbsteinschreibung zugewiesen werden soll.';
$string['deleteselectedusers'] = 'Ausgewählte Selbsteinschreibungen löschen';
$string['editselectedusers'] = 'Ausgewählte Selbsteinschreibungen bearbeiten';
$string['enablewaitlist'] = 'Warteliste aktivieren';
$string['enablewaitlist_help'] = 'Wenn die Warteliste aktiviert ist werden Nutzer/innen der Warteliste hinzugefügt, sobald die Anzahl der maximalen Einschreibung überschritten wird.';
$string['enrolchancemissed'] = 'Der Platz ist leider schon vergeben, das tut uns leid. Bitte Sie es beim nächsten Mal wieder.';
$string['enrolenddate'] = 'Einschreibungsende';
$string['enrolenddate_help'] = 'Wenn diese Option aktiviert ist, können Nutzer/innen sich bis zum angegebenen Zeitpunkt selbst einschreiben.';
$string['enrolenddaterror'] = 'Das Einschreibungsende muss nach dem Einschreibungsbeginn liegen.';
$string['enrolme'] = 'Einschreiben';
$string['enrolperiod'] = 'Teilnahmedauer';
$string['enrolperiod_desc'] = 'Die Teilnahmedauer ist die Zeitdauer, in der die Einschreibung gültig ist. Wenn diese Option deaktiviert wird, ist die Teilnahme unbefristet.';
$string['enrolperiod_help'] = 'Die Teilnahmedauer ist die Zeitdauer, in der die Einschreibung gültig ist, beginnend mit dem Moment der Einschreibung. Wenn diese Option deaktiviert wird, ist die Teilnahmedauer unbegrenzt.';
$string['enrolperiodcountfrom'] = 'Zeitraum beginnt von';
$string['enrolperiodcountfrom_help'] = 'Der Zeitraum seit Abschluss des Kurses kann entweder von der aktuellen Zeit rückwärts zählen oder seit Einschreibungsbeginn. In jedem Fall zählt der Tag und nicht die Uhrzeit.';
$string['enrolperiodcountfromnow'] = 'aktuelle Zeit';
$string['enrolperiodcountfromenrollstart'] = 'Einschreibungsbeginn';
$string['enrolstartdate'] = 'Einschreibungsbeginn';
$string['enrolstartdate_help'] = 'Legen Sie fest, was nach dem Ablauf der Einschreibung in einem Kurs passiert. Denken Sie daran, dass bei der Austragung von Nutzer/innen einige Daten nicht mehr verfügbar sind.';
$string['enrolwaitlistuser'] = '"{$a->user}" in "{$a->course}" einschreiben';
$string['enrolwaitlistuserconfirm'] = 'Möchten Sie wirklich "{$a->user}" manuell in den Kurs "{$a->course}" einschreiben?';
$string['expiredaction'] = 'Aktion bei Ablauf der Kurseinschreibung';
$string['expiredaction_help'] = 'Legen Sie fest, was nach dem Ablauf der Einschreibung in einem Kurs passiert. Denken Sie daran, dass bei der Austragung von Nutzer/innen einige Daten nicht mehr verfügbar sind.';
$string['expirymessageenrollersubject'] = 'In Kürze endet Ihr Kurs';
$string['expirymessageenrollerbody'] = 'Guten Tag,

im Kurs "{$a->course}" läuft für folgende Nutzer/innen innerhalb der nächsten  {$a->threshold} die Einschreibung ab:

{$a->users}

Sie können auf der folgenden Seite die Teilnahmedauer indivduell verlängern oder entfristen: {$a->extendurl}

Ihr E-Learning-Team';
$string['expirymessageenrolledsubject'] = 'In Kürze endet Ihr Kurs';
$string['expirymessageenrolledbody'] = 'Guten Tag {$a->user},

Sie sind derzeit im Kurs "{$a->course}" eingeschrieben. Die Teilnahmedauer läuft am {$a->timeend} ab. Danach ist ein Zugriff auf den Kurs nicht mehr möglich.

Wenn Sie Fragen haben, wenden Sie sich bitte an {$a->enroller}.

Ihr E-Learning-Team';
$string['expirynotifyall'] = 'Trainer/in und eingeschriebene Nutzer/innen';
$string['expirynotifyenroller'] = 'Nur Trainer/in';
$string['joinwaitlist'] = 'Warteliste beitreten';
$string['joinwaitlistmessage'] = 'Sie können der Warteliste beitreten. Sobald ein Platz frei wird, werden Sie per E-Mail informiert.';
$string['leavewaitlist'] = 'Warteliste verlassen';
$string['longtimenosee'] = 'Inaktive abmelden';
$string['longtimenosee_help'] = 'Wenn Personen lange Zeit nicht mehr auf den Kurs zugegriffen haben, werden sie automatisch abgemeldet. Dieser Parameter legt die maximale Inaktivitätsdauer fest.';
$string['maxenrolled'] = 'Maximale Einschreibungen';
$string['maxenrolled_help'] = 'Diese Option legt die maximale Anzahl der erlaubten Nutzer/innen mit Selbsteinschreibung fest (0 = unbeschränkt).';
$string['maxenrolledreached'] = 'Die maximale Anzahl der erlaubten Nutzer/innen mit Selbsteinschreibung ist bereits erreicht.';
$string['messageprovider:expiry_notification'] = 'Systemnachricht zum Ablauf von Selbsteinschreibungen';
$string['messageprovider:waitlist_notification'] = 'Systemnachricht über freie Plätze auf der Warteliste von "Einschreibung nach Kursbereich"';
$string['newenrols'] = 'Selbsteinschreibung erlauben';
$string['newenrols_desc'] = 'Nutzer/innen dürfen sich selbst einschreiben';
$string['newenrols_help'] = 'Diese Option legt fest, ob Nutzer/innen sich selber in diesen Kurs einschreiben dürfen.';
$string['nocategory'] = 'keine Einschränkung';
$string['nocourseincategory'] = 'Um Zugang zu diesem Kurs zu erhalten, müssen bereits einen Kurs aus dem Kursbereich "{$a}" abgeschlossen haben.';
$string['nocourseincategorysince'] = 'Um Zugang zu diesem Kurs zu erhalten, müssen bereits einen Kurs aus dem Kursbereich "{$a}" abgeschlossen haben oder ihr Kursabschluss liegt zu weit in der Vergangenheit.';
$string['nogroup'] = 'Keine Gruppeneinschränkung';
$string['notifiedcount'] = 'Benachrichtigt ohne Reaktion';
$string['onwaitlistsince'] = 'Auf Wartelist seit';
$string['pluginname'] = 'Einschreibung nach Kursbereich';
$string['pluginname_desc'] = 'Das Plugin "Einschreibung nach Kursbereich" erlaubt es Nutzer/innen, selber einen Kurs zur Teilnahme auszuwählen. Kurse können von einem Kurs aus einem festgelegten Kursbereich abhängig gemacht werden. Zusätzlich bietet das Plugin eine Wartelistenfunktion an. Intern nutzt die Selbsteinschreibung das Plugin "Manuelle Einschreibung", welches deswegen im Kurs ebenfalls aktiviert sein muss.';
$string['privacy:metadata'] = 'Das Plugin "Einschreibung nach Kursbereich" speichert keine personenbezogenen Daten.';
$string['removewaitlistuser'] = 'Nutzer/in von der Warteliste entfernen';
$string['removewaitlistuserconfirm'] = 'Möchten Sie wirklich "{$a->user}" von der Warteliste des Kurses "{$a->course}" entfernen?';
$string['role'] = 'Rolle im Kurs';
$string['bycategory:config'] = 'Instanzen von Einschreibung nach Kursbereich konfigurieren';
$string['bycategory:enrolself'] = 'Selbsteinschreibung im Kurs';
$string['bycategory:manage'] = 'Eingeschriebene Nutzer/innen verwalten';
$string['bycategory:unenrol'] = 'Nutzer/innen aus dem Kurs abmelden';
$string['bycategory:unenrolself'] = 'Selbst aus dem Kurs abmelden';
$string['sendcoursewelcomemessage'] = 'Begrüßungstext versenden';
$string['sendcoursewelcomemessage_help'] = 'Wenn ein Nutzer/innen sich in den Kurs einschreiben, kann ihnen eine Begrüßungsnachricht gesendet werden. Wenn diese vom Kurskontakt geschickt wird (voreingestellt ist Trainer/in) und mehrere Personen diese Rolle haben, wird die E-Mail von der Person versendet, der diese Rolle zuerst zugewiesen wurde.';
$string['sendexpirynotificationstask'] = 'Systemnachricht zum Ablauf von Selbsteinschreibungen';
$string['sendwaitlistnotificationstask'] = 'Systemnachricht über frei gewordene Plätze an Nutzer/innen auf Wartelisten';
$string['status'] = 'Existierende Einschreibungen erlauben';
$string['status_desc'] = 'Selbsteinschreibung für neue Kurse aktivieren';
$string['status_help'] = 'Wenn diese Option aktiviert und "Neue Einschreibungen erlauben" deaktiviert ist, können alle vorhandenen Selbsteinschreibungen weiter auf den Kurs zugreifen. Wenn die Option deaktiviert ist, werden alle vorhandenen Selbsteinschreibungen deaktiviert und keine neuen Selbsteinschreibungen zugelassen.';
$string['syncenrolmentstask'] = 'Selbsteinschreibung synchronisieren';
$string['tokeninvalid'] = 'Der Link ist nicht gültig oder abgelaufen. Bitte klicken Sie den Link aus Ihrer Email oder stellen Sie sicher, dass er komplett kopiert wurde, bevor Sie ihn in den Browser einfügen. Wenn Ihre Email älter als 24 Stunden ist, dann ist der Link bereits abgelaufen.';
$string['unenrol'] = 'Nutzer/in abmelden';
$string['unenrolselfconfirm'] = 'Möchten Sie sich selbst wirklich vom Kurs "{$a}" abmelden?';
$string['unenroluser'] = 'Möchten Sie "{$a->user}" wirklich vom Kurs "{$a->course}" abmelden?';
$string['unenrolusers'] = 'Nutzer/innen abmelden';
$string['usernotonwaitlist'] = 'Sie sind nicht auf der Warteliste dieses Kurses.';
$string['waitlist'] = 'Warteliste';
$string['waitlist_active'] = '{$a} Teilnehmer auf der Warteliste';
$string['waitlist_blocked_message'] = 'Sie wurden bereits 5 Mal, ohne Reaktion, über einen freien Platz informiert.

Sie werden keine weiteren Benachrichtigungen erhalten.

Wenn Sie immernoch interessiert sind sich in diesen Kurs einzuschreiben, verlassen Sie bitte die Warteliste und schreiben Sie sich erneut ein.
Sie werden dadurch wieder Benachrichtigungen erhalten, werden aber auch an das Ende der Warteliste gesetzt.';
$string['waitlist_deactivated'] = 'Warteliste ist nicht aktiv';
$string['waitlist_info_message'] = 'Sie werden per Email benachrichtigt, sobald ein Platz verfügbar wird und erhalten dann die Möglichkeit, sich in den Kurs einzuschreiben.
Bitte beachten Sie, dass weitere Teilnehmer*innen ebenfalls informiert werden, also seien Sie schnell.
Die Email wird täglich versendet, wenn mindestens ein Platz verfügbar ist.

Ihre aktuelle Position auf der Warteliste ist: {$a->waitlistposition}.
Wenn Sie nicht mehr länger warten möchten, nutzen Sie die nachfolgende Schaltfläche um die Warteliste zu verlassen.
';
$string['waitlist_users'] = 'Teilnehmer*innen auf der Warteliste';
$string['waitlistnotifycount'] = 'Anzahl der Teilnehmer*innen die über einen freien Platz informiert werden.';
$string['waitlistnotifycount_help'] = 'Bis zu X Teilnehmer*innen werden von der Warteliste werden über einen freien Platz informiert. Die Teilnehmer*in die zuerst reagiert erhält den Platz.';
$string['waitlistnotifylimit'] = 'Maximale Anzahl an Benachrichtigungen die an eine Teilnehmer*in auf der Warteliste gesendet werden';
$string['waitlistnotifylimit_help'] = 'Teilnehmer*innen auf der Warteliste erhalten nur eine bestimmte Anzahl an Benachrichtigungen, danach werden die Teilnehmer*innen ignoriert.';
$string['waitlist_notification_body'] = 'Hallo {$a->userfullname},
es ist ein Platz im Kurs {$a->coursename} verfügbar.

Klicken Sie auf den folgenden Link um sich, sofern der Platz dann immer noch verfügbar ist, einzuschreiben, .
{$a->confirmenrolurl}

Diese Email wurde noch an bis zu {$a->notifyamount} weitere Teilnehmer*innen gesendet. Seien Sie also schnell, um den Platz zu erhalten.

Wenn sie nicht mehr interessiert sind dem Kurs "{$a->coursename}" beizutreten, klicken Sie den folgenden Link, um sich von der Warteliste zu entfernen.
{$a->leavewaitlisturl}
';
$string['waitlist_notification_subject'] = 'Es ist ein Platz in Kurs "{$a->coursename}" verfügbar';
$string['waitlist_status_info'] = 'Bis zu {$a->notifycount} Teilnehmer von der Warteliste werden am {$a->nextruntime} über einen freien Platz in diesem Kurs informiert.
Teilnehmer werden nur {$a->notifylimit} Mal informiert. Wenn sie bis dahin nicht reagieren werden sie ignoriert';
$string['welcometocourse'] = 'Willkommen zu {$a}';
$string['welcometocoursetext'] = 'Willkommen im Kurs "{$a->coursename}"!

Falls Sie es nicht bereits erledigt haben, sollten Sie Ihr persönliches Nutzerprofil bearbeiten. Auf diese Weise können wir alle mehr über Sie erfahren und besser zusammenarbeiten:

{$a->profileurl}';
$string['wrongtokenuser'] = 'Der Link war für eine andere Teilnehmer*in bestimmt. Bitte warten Sie bis Sie Ihre Benachrichtigung erhalten.';
$string['youareonthewaitlist'] = 'Sie befinden sich aktuell auf der Warteliste.';
