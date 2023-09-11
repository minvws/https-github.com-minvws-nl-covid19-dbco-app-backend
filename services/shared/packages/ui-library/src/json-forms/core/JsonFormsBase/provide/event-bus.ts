import { inject, provide } from 'vue';
import type { EventBus } from '../../../../utils/create-event-bus/create-event-bus';
import { createEventBus } from '../../../../utils/create-event-bus/create-event-bus';
import type { ChildFormChangeEvent, FormLinkEvent } from '../../../types';

export type FormEvents = {
    formLink: FormLinkEvent;
    childFormChange: ChildFormChangeEvent;
};

const key = Symbol('event-bus');

export function provideEventBus() {
    const eventBus = createEventBus<FormEvents>();
    provide(key, eventBus);
    return { eventBus };
}

export function injectEventBus() {
    return {
        eventBus: inject(key) as EventBus<FormEvents>,
    };
}
