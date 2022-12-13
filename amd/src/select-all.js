import Selectors from './local/select-all/selectors';

const registerEventListeners = () => {
  const masterElem = document.querySelector(Selectors.selectors.masterSelector);
  const checkboxElems = Array.from(document.querySelectorAll(Selectors.selectors.checkboxesSelector));

  if(!masterElem) {
    throw new Error('select all element doesn\'t exist');
  }

  masterElem.addEventListener('change', e => {
    const value = e.target.checked;
    checkboxElems.forEach((elem) => {
      elem.checked = value;
    });
  });

  document.addEventListener('change', e => {
    if(e.target === masterElem || checkboxElems.includes(e.target) === false) {
      return;
    }

    masterElem.indeterminate = true;
    masterElem.checked = false;
  });
};

export const init = () => {
  registerEventListeners();
};
