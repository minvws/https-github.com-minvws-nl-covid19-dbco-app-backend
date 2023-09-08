import { createEventBus } from './create-event-bus';
import { faker } from '@faker-js/faker';

describe('creatEventBus', () => {
    it('should be able to add, listen and remove event listeners', () => {
        const { $on, $off, $emit } = createEventBus();
        const eventType = faker.lorem.word();

        const handler = vi.fn();

        $on(eventType, handler);
        $emit(eventType);

        $off(eventType, handler);
        $emit(eventType);

        expect(handler).toHaveBeenCalledOnce();
    });
});
