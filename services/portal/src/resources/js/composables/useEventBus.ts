import { createEventBus } from '@dbco/ui-library';

type KnownEvents = {
    'open-osiris-modal': void;
    'policy-status-change': void;
};

const eventBus = createEventBus<KnownEvents>();

export function useEventBus() {
    return eventBus;
}
