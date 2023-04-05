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
 * Confirm handler which uses show_confirm_dialog and allowing callback actions
 *
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from './local/confirm/selectors';

const registerEventListeners = () => {
    document.addEventListener('click', e => {
        let dataholder;
        if (e.target && (dataholder = e.target.closest(Selectors.actions.confirmButton))) {
            M.util.show_confirm_dialog(e, {
                message: dataholder.dataset.message,
                callback: () => {
                    const targetUrl = new URL(dataholder.getAttribute('href'));
                    targetUrl.searchParams.append('sesskey', dataholder.dataset.sesskey);
                    targetUrl.searchParams.append('confirm', 1);
                    window.location = targetUrl.toString();
                }
            });
        }
    });
};

export const init = () => {
    registerEventListeners();
};
