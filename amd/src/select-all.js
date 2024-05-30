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
 * JS handler for select all functionality
 *
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from './local/select-all/selectors';

const registerEventListeners = () => {
    const masterElem = document.querySelector(Selectors.selectors.masterSelector);
    const checkboxElems = Array.from(document.querySelectorAll(Selectors.selectors.checkboxesSelector));
    /** @var HTMLButtonElement */
    const submitButtonElem = document.querySelector(Selectors.selectors.submitSelector);
    submitButtonElem.disabled = true;

    if (!masterElem) {
        throw new Error('select all element doesn\'t exist');
    }

    masterElem.addEventListener('change', e => {
        const state = e.target.checked;

        submitButtonElem.disabled = !state;

        if (state === true) {
            checkAll();
        } else {
            uncheckAll();
        }
    });

    document.addEventListener('change', e => {
        // Ignore masterElem or other Elements which are not in checkBoxElems Array.
        if (e.target === masterElem || checkboxElems.includes(e.target) === false) {
            return;
        }

        const allChecked = checkboxElems.every((elem) => elem.checked === true);
        const allUnchecked = checkboxElems.every((elem) => elem.checked === false);

        submitButtonElem.disabled = allUnchecked;

        if (allChecked === true) {
            masterToChecked();
            return;
        }

        if (allUnchecked === true) {
            masterToUnchecked();
            return;
        }

        masterToIndeterminate();
    });

    const checkAll = () => {
        checkboxElems.forEach((elem) => {
            elem.checked = true;
        });
    };

    const uncheckAll = () => {
        checkboxElems.forEach((elem) => {
            elem.checked = false;
        });
    };

    const masterToIndeterminate = () => {
        masterElem.indeterminate = true;
        masterElem.checked = false;
    };

    const masterToChecked = () => {
        masterElem.indeterminate = false;
        masterElem.checked = true;
    };

    const masterToUnchecked = () => {
        masterElem.indeterminate = false;
        masterElem.checked = false;
    };
};

export const init = () => {
    registerEventListeners();
};
