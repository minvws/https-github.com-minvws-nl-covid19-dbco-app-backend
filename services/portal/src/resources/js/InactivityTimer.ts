import { authApi } from '@dbco/portal-api';
import { sub } from 'date-fns';
import intervalToDuration from 'date-fns/intervalToDuration';
import { debounce, delay } from 'lodash';
import { parseDate } from './utils/date';

/* This functionality is covered by integration tests, becaue it relies heavily on the browser and backend */
/* c8 ignore start */

/**
 * The backend sets a cookie with an expiry for when the frontend should logout the current user.
 * This cookie is only set on routes where you need to be logged in.
 *
 * This plugin updates the exipiration date (via the backend) when there is user activity and shows
 * a modal when it is almost reached. When the expiration date is due; it will forcefully logout the user.
 *
 * Note: the inactivity-timer expiry date is not always equal to the sesion expiry date.
 *
 **/
export default {
    install() {
        setInterval(whenExpiryExists(checkIfExpired), 1000);
        window.addEventListener('click', whenExpiryExists(registerActivity));
        window.addEventListener('keydown', whenExpiryExists(registerActivity));
    },
};

// This debounce duration is a trade-off between the number of backend calls and accuracy
// for logout.
// Every duration below the lifetime (30 minutes) will be sufficient to not be logged out.
// But if you register activity during the debounce interval, it will be pushed to the end
// of the interval, thus registering the activity on a later moment than it occured.
const registerActivity = debounce(() => delay(authApi.refreshSession, 5 * 1000), 5 * 60 * 1000, { leading: true });

const whenExpiryExists = (fn: (expiryDate: Date) => void) => () => {
    const app = window.app;
    if (!app) return;
    if (!app.$cookies.isKey('InactivityTimerExpiryDate')) return;

    const expiryDate = parseDate(app.$cookies.get('InactivityTimerExpiryDate'));
    fn(expiryDate);
};

const logout = () => {
    window.location.replace('/logout');
};

let modalOpen = false;
let loggedOut = false;

let modalClosed = false;
const closeModal = async () => {
    // Do not reopen the modal when closed
    modalClosed = true;

    await authApi.refreshSession();

    // Modal will not be repoened because the timer is reset
    modalClosed = false;
};

const checkIfExpired = (expiryDate: Date) => {
    const now = new Date();

    // When the expiry has been reached, we forcefully logout.
    if (now >= expiryDate && !loggedOut) {
        loggedOut = true;
        logout();
        return;
    }

    // When the expiry is near, we show a modal to notify that the user will be logged out eventually.
    const timeToShowModal = sub(expiryDate, { minutes: 3 });
    const showModal = now >= timeToShowModal;

    // We need to reopen the modal every second because the time is not reactive. Would be nice to improve.
    if (showModal && !modalClosed) {
        modalOpen = true;
        const timeToLogout = differenceInMinutesAndSecond(now, expiryDate);
        window.app.$modal.show({
            title: 'De sessie is bijna verlopen',
            text: `Je hebt bijna 30 minuten niets gedaan in het BCO Portaal. Daarom word je automatisch uitgelogd over ${timeToLogout} minuten. Klik op ‘Verlengen’ om verder te gaan.`,
            okTitle: 'Verlengen',
            onConfirm: closeModal,
            onCancel: closeModal,
        });
        return;
    }

    // When the modal is open, but the user registered activity we can close the modal again.
    if (modalOpen && !showModal) {
        window.app.$modal.hide();
        modalOpen = false;
    }
};

const differenceInMinutesAndSecond = (leftDate: Date, rightDate: Date): string => {
    const interval = intervalToDuration({
        start: leftDate,
        end: rightDate,
    });
    const minutes = interval.minutes?.toString().padStart(2, '0');
    const seconds = interval.seconds?.toString().padStart(2, '0');

    return `${minutes}:${seconds}`;
};

/* c8 ignore stop */
