import { flushCallStack } from '../../test';
import { usePolling } from './use-polling';

describe('use-polling', () => {
    const mockRequest = vi.fn(() => Promise.resolve());

    beforeEach(() => {
        vi.clearAllTimers();
        vi.clearAllMocks();
        vi.useFakeTimers();
    });

    afterAll(() => {
        vi.useRealTimers();
    });

    it('polling does not start automatically', () => {
        const { isPolling } = usePolling({
            request: () => mockRequest(),
            continuePolling: () => false,
        });

        expect(isPolling.value).toBe(false);
        expect(mockRequest).not.toHaveBeenCalled();
    });

    it('should start polling when start is called', () => {
        const { startPolling, isPolling } = usePolling({
            request: () => mockRequest(),
            continuePolling: () => true,
        });
        expect(isPolling.value).toBe(false);

        startPolling();

        expect(isPolling.value).toBe(true);
        expect(mockRequest).toHaveBeenCalledOnce();
    });

    it('last poll request is ran on the maxTime configuration, and polling is stopped', async () => {
        vi.spyOn(window, 'setTimeout');
        const onTimeout = vi.fn();

        const { startPolling, isPolling } = usePolling({
            request: () => mockRequest(),
            continuePolling: () => true,
            interval: 1000,
            timeout: 2500,
            onTimeout,
        });

        startPolling();
        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(1);
        expect(window.setTimeout).toHaveBeenCalledTimes(1);
        expect(window.setTimeout).toHaveBeenLastCalledWith(expect.any(Function), 1000);

        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(2);
        expect(window.setTimeout).toHaveBeenCalledTimes(2);
        expect(window.setTimeout).toHaveBeenLastCalledWith(expect.any(Function), 1000);

        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(3);
        expect(window.setTimeout).toHaveBeenCalledTimes(3);
        expect(window.setTimeout).toHaveBeenLastCalledWith(expect.any(Function), 500);

        vi.runAllTimers();
        await flushCallStack();

        expect(isPolling.value).toBe(false);
        expect(onTimeout).toHaveBeenCalledOnce();
    });

    it('keeps polling until check returns false', async () => {
        const continuePolling = vi.fn(() => true);

        const { startPolling, isPolling } = usePolling({
            request: () => mockRequest(),
            continuePolling,
        });

        startPolling();
        expect(isPolling.value).toBe(true);

        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(1);

        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(2);

        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(3);

        expect(isPolling.value).toBe(true);
        continuePolling.mockImplementationOnce(() => false);

        vi.runAllTimers();
        await flushCallStack();

        expect(isPolling.value).toBe(false);
        expect(mockRequest).toHaveBeenCalledTimes(4);

        vi.runAllTimers();
        await flushCallStack();

        expect(mockRequest).toHaveBeenCalledTimes(4);
    });

    it('request is canceled and timeout is cleared when polling is stopped', async () => {
        vi.spyOn(global, 'clearTimeout');
        let lastSignal: AbortSignal | null = null;

        const { startPolling, stopPolling, isPolling } = usePolling({
            request: (signal) => {
                lastSignal = signal;
                return mockRequest();
            },
            continuePolling: () => true,
        });

        startPolling();
        expect(isPolling.value).toBe(true);
        expect(clearTimeout).not.toHaveBeenCalled();
        expect(lastSignal!.aborted).toBe(false);

        stopPolling();
        expect(isPolling.value).toBe(false);
        expect(clearTimeout).toHaveBeenCalled();
        expect(lastSignal!.aborted).toBe(true);
    });
});
