import type { AxiosError } from 'axios';
import { errorInterceptor } from './errorInterceptor';
import http from 'http';
import ignoreList from './errorInterceptor.ignoreList';
import { createTestingPinia } from '@pinia/testing';
import { setActivePinia } from 'pinia';
import { useAppStore } from '@/store/app/appStore';

vi.unmock('@/interceptors/errorInterceptor');

const testPinia = createTestingPinia({ stubActions: false });

describe('errorInterceptor', () => {
    beforeEach(() => {
        vi.resetAllMocks();
        setActivePinia(testPinia);
    });

    const { setHasPermissionError, setHasError } = useAppStore();

    const httpCodes = Object.keys(http.STATUS_CODES).map((code) => parseInt(code, 10));
    const nonErrorCodes = [undefined, ...httpCodes.filter((code) => code < 400 || [404, 409, 410, 422].includes(code))];
    const errorCodes = httpCodes.filter((code) => !nonErrorCodes.includes(code));

    it.each(nonErrorCodes)('should not dispatch app/REQUEST_ERROR on %s error', (status) => {
        errorInterceptor({ response: { status } } as AxiosError).catch(() => {});

        expect(setHasError).not.toHaveBeenCalled();
    });

    it('should not dispatch app/REQUEST_ERROR when config.url matches an URL in the ignoreList', () => {
        errorInterceptor({ response: { config: { url: ignoreList[0] }, status: 500 } } as AxiosError).catch(() => {});

        expect(setHasError).not.toHaveBeenCalled();
    });

    it.each(errorCodes)('should dispatch app/REQUEST_ERROR on %f error', async (status) => {
        await errorInterceptor({ response: { status } } as AxiosError).catch(() => {});
        if (status !== 403) {
            expect(setHasError).toHaveBeenCalledWith(true);
        }
    });
    it('should dispatch app/PERMISSION_ERROR on 403 error', () => {
        errorInterceptor({ response: { status: 403 } } as AxiosError).catch(() => {});

        expect(setHasPermissionError).toHaveBeenCalledWith(true);
    });
});
