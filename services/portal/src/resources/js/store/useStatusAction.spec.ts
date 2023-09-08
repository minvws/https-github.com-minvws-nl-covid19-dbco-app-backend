import useStatusAction, { isIdle, isPending, isRejected, isResolved } from './useStatusAction';

describe('useStatusAction', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });
    it('should idle at start', () => {
        const { status } = useStatusAction(() => Promise.resolve());
        expect(isIdle(status.value)).toBe(true);
    });

    it('should be pending when called', () => {
        const { status, action } = useStatusAction(() => Promise.resolve());

        // eslint-disable-next-line @typescript-eslint/no-floating-promises
        action();

        expect(isPending(status.value)).toBe(true);
    });

    it('should resolve with result in promise and result', async () => {
        const { status, action } = useStatusAction(() => Promise.resolve('result'));

        const result = await action();

        if (!isResolved(status.value)) throw Error();
        expect(result).toBe('result');
        expect(status.value.result).toBe(result);
    });

    it('should reject with error', async () => {
        const { status, action } = useStatusAction(() => Promise.reject(new Error('Error')));

        await action();

        if (!isRejected(status.value)) throw Error();
        expect(status.value.error.message).toBe('Error');
    });

    it('should reject with fallback error', async () => {
        const { status, action } = useStatusAction(() => Promise.reject('Not an error'));

        await action();

        if (!isRejected(status.value)) throw Error();
        expect(status.value.error.message).toBe('');
    });
});
