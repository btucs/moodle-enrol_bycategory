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
