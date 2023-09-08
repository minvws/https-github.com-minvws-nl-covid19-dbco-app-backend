import Vue from 'vue';

export type EventBus<KnownEvents extends Record<string, unknown>> = {
    $on: <T extends keyof KnownEvents>(event: T, callback: (payload: KnownEvents[T]) => void) => void;
    $off: <T extends keyof KnownEvents>(event: T, callback: (payload: KnownEvents[T]) => void) => void;
    $emit: <T extends keyof KnownEvents>(
        event: T,
        ...args: undefined extends KnownEvents[T] ? [] : [KnownEvents[T]]
    ) => void;
};

/**
 * Creates a new event bus with some added type safety
 * @example
 *
 *  const eventBus = createEventBus<{
 *      openForm: boolean,
 *      closeForm: number
 *  }>();
 *  eventBus.$emit('openForm', true) // ok
 *  eventBus.$on('closeForm', (id: number) => {}) // ok
 *  eventBus.$on('closeForm', (id: boolean) => {}) // type error
 *  eventBus.$on('openForm', (shouldBeBoolean: string) => {}) // type error
 *  eventBus.$on('foobar') // type error
 */
export function createEventBus<Events extends Record<string, unknown>>() {
    const eventBusVue = new Vue();
    const { $on, $off, $emit } = eventBusVue;
    const eventBus = {
        $on: $on.bind(eventBusVue),
        $off: $off.bind(eventBusVue),
        $emit: $emit.bind(eventBusVue),
    } as EventBus<Events>;

    return eventBus;
}
