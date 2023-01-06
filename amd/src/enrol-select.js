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
 * Connect course select box with enrol methods select box
 *
 * @copyright  2022 Matthias Tylkowski <matthias.tylkowski@b-tu.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getEnrolmentMethods} from './repository';
import Selectors from './local/enrol-select/selectors';

const registerEventListeners = (defaultSelected) => {
  const sourceSelectElem = document.querySelector(Selectors.selectors.sourceSelector);
  const targetSelectElem = document.querySelector(Selectors.selectors.targetSelector);

  if (!sourceSelectElem || !targetSelectElem) {
    throw new Error('source or target select element could not be found');
  }

  sourceSelectElem.addEventListener('change', e => {
    const targetCourseId = e.target.value;
    updateSelectOptions(targetSelectElem, targetCourseId, defaultSelected);
  });

  updateSelectOptions(targetSelectElem, sourceSelectElem.value, defaultSelected);
};

/**
 * Update the <select> element with custom data
 * @param {HTMLSelectElement} targetSelectElement
 * @param {number} targetCourseId
 * @param {number} defaultSelected enrolment id which is selected by default, if available
 */
const updateSelectOptions = async(targetSelectElement, targetCourseId, defaultSelected) => {
  const data = await getEnrolmentMethods(targetCourseId);

  // Clear existing options.
  targetSelectElement.options.length = 0;
  addOptions(data, targetSelectElement, defaultSelected);
};

/**
 * Add Options from data to the selectElement
 * @param {Array<{id: string, name: string}>} data
 * @param {HTMLSelectElement} selectElement
 * @param {number} defaultSelected
 */
const addOptions = (data, selectElement, defaultSelected) => {
  const collator = new Intl.Collator('de-DE');

  data.sort((a, b) => collator.compare(a.name, b.name));

  for (const item of data) {
    const option = new Option(item.name, item.id, undefined, item.id === defaultSelected);
    selectElement.add(option);
  }
};

/**
 * Initialize Module
 * @param {number} defaultSelected enrolment id to preselect
 */
export const init = (defaultSelected) => {
  registerEventListeners(defaultSelected);
};
