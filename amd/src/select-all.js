import Selectors from './local/select-all/selectors';

const registerEventListeners = () => {
    const masterElem = document.querySelector(Selectors.selectors.masterSelector);
    const checkboxElems = Array.from(document.querySelectorAll(Selectors.selectors.checkboxesSelector));

    if (!masterElem) {
        throw new Error('select all element doesn\'t exist');
    }

    masterElem.addEventListener('change', e => {
        const state = e.target.checked;
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
