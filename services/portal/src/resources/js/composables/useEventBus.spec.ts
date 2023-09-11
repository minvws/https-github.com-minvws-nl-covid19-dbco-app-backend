import { fakerjs } from '@/utils/test';
import { useEventBus } from './useEventBus';

describe('useEventBus', () => {
    it('should be able to add, listen and remove event listeners', () => {
        const { $on, $off, $emit } = useEventBus();
        const eventType = fakerjs.lorem.word();

        const handler = vi.fn();

        // `eventType` is casted as any, because only known (typed) events should be used in normal usage to make it more type safe.

        $on(eventType as any, handler); // eslint-disable-line @typescript-eslint/no-explicit-any
        $emit(eventType as any); // eslint-disable-line @typescript-eslint/no-explicit-any

        $off(eventType as any, handler); // eslint-disable-line @typescript-eslint/no-explicit-any
        $emit(eventType as any); // eslint-disable-line @typescript-eslint/no-explicit-any

        expect(handler).toHaveBeenCalledOnce();
    });
});
