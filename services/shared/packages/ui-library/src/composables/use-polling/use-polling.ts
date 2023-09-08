import { ref } from 'vue';
import { isFunction } from 'lodash';

/**
 * A callback that should trigger the poll request. It should use an the `AbortSignal`
 * that is provided so any pending request can be stopped if the polling is stopped.
 */
type PollRequest<Response> = (signal: AbortSignal) => Promise<Response>;

/**
 * A function that will be called after every poll.
 * Return true to keep polling - or - false to stop.
 */
type PollResponseCheck<Response> = (reponse: Response) => boolean;

type PollingConfig<Response> = {
    request: PollRequest<Response>;
    continuePolling: PollResponseCheck<Response>;
    interval?: number;
    timeout?: number;
    onTimeout?: () => void;
};

export function usePolling<Response>(config: PollingConfig<Response>) {
    const { request, continuePolling, interval = 1000, timeout = 60 * 1000, onTimeout } = config;

    let intervalTimeout = -1;
    let startTime: number;
    let controller: AbortController | null;
    const isPolling = ref(false);

    const poll = async () => {
        let response: Response;
        controller = new AbortController();
        try {
            response = await request(controller.signal);
            /* c8 ignore next 7 */
        } catch (error) {
            // ignore AbortError's, otherwise throw
            if (controller && controller.signal.aborted) {
                return;
            }
            stopPolling();
            throw error;
        }
        controller = null;

        if (!continuePolling(response)) {
            stopPolling();
            return;
        }

        const nextPollTimeout = Math.min(interval, Math.max(startTime + timeout - Date.now(), 0));
        if (nextPollTimeout <= 0) {
            stopPolling();
            if (isFunction(onTimeout)) {
                onTimeout();
            }
            return;
        }

        intervalTimeout = window.setTimeout(poll, nextPollTimeout);
        return;
    };

    const startPolling = () => {
        startTime = Date.now();
        isPolling.value = true;
        void poll();
    };

    const stopPolling = () => {
        if (controller) {
            controller.abort();
        }
        if (intervalTimeout) {
            clearTimeout(intervalTimeout);
        }
        isPolling.value = false;
    };

    return { startPolling, stopPolling, isPolling };
}
