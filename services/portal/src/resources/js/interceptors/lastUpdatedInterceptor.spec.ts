import type { AxiosError, AxiosResponse, AxiosRequestConfig, InternalAxiosRequestConfig } from 'axios';
import axios from 'axios';
import {
    lastUpdatedErrorInterceptor,
    lastUpdatedRequestInterceptor,
    lastUpdatedResponseInterceptor,
} from './lastUpdatedInterceptor';
import { userCanEdit } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import { useAppStore } from '@/store/app/appStore';
import { createTestingPinia } from '@pinia/testing';
import { setActivePinia } from 'pinia';
vi.mock('@/utils/interfaceState');

// remove lastUpdatedInterceptor mocks from vi-setup.ts
vi.unmock('@/interceptors/lastUpdatedInterceptor');
vi.resetModules();

const testPinia = createTestingPinia({ stubActions: false });

beforeEach(() => {
    vi.resetAllMocks();
    setActivePinia(testPinia);
});

describe('lastUpdatedRequestInterceptor', () => {
    const { handleRequestStart } = useAppStore();
    beforeEach(() => {
        (handleRequestStart as Mock).mockReset();
        (userCanEdit as Mock).mockReset();
    });

    it('should not dispatch events for get requests', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => false);
        lastUpdatedRequestInterceptor({ method: 'get' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).not.toHaveBeenCalled();
    });

    it('should not dispatch post requests when covidCase is view only', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => false);

        lastUpdatedRequestInterceptor({ method: 'post' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).not.toHaveBeenCalled();
    });

    it('should not dispatch post requests when covidCase is view only', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => false);

        lastUpdatedRequestInterceptor({ method: 'put' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).not.toHaveBeenCalled();
    });

    it('should dispatch events for delete requests', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        lastUpdatedRequestInterceptor({ method: 'delete' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).toHaveBeenCalled();
    });

    it('should dispatch events for post requests', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        lastUpdatedRequestInterceptor({ method: 'post' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).toHaveBeenCalled();
    });

    it('should dispatch events for put requests', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        lastUpdatedRequestInterceptor({ method: 'put' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).toHaveBeenCalled();
    });

    it('should handle configs with capitalized method strings', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        lastUpdatedRequestInterceptor({ method: 'PUT' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).toHaveBeenCalled();
    });

    it('should ignore session refreshes', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        lastUpdatedRequestInterceptor({ method: 'post', url: '/api/session-refresh' } as InternalAxiosRequestConfig);

        expect(handleRequestStart).not.toHaveBeenCalled();
    });

    it('should ignore case lock refreshes', () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        lastUpdatedRequestInterceptor({
            method: 'post',
            url: '/api/cases/some-uuid/lock/refresh',
        } as InternalAxiosRequestConfig);

        expect(handleRequestStart).not.toHaveBeenCalled();
    });
});

describe('lastUpdatedResponseInterceptor', () => {
    const { handleRequestComplete } = useAppStore();

    beforeEach(() => {
        (handleRequestComplete as Mock).mockReset();
    });

    it('should not dispatch events for get requests', () => {
        lastUpdatedResponseInterceptor({ config: { method: 'get' } } as AxiosResponse);

        expect(handleRequestComplete).not.toHaveBeenCalled();
    });

    it('should dispatch events for delete requests', () => {
        lastUpdatedResponseInterceptor({ config: { method: 'delete' } } as AxiosResponse);

        expect(handleRequestComplete).toHaveBeenCalledWith(true);
    });

    it('should dispatch events for post requests', () => {
        lastUpdatedResponseInterceptor({ config: { method: 'post' } } as AxiosResponse);

        expect(handleRequestComplete).toHaveBeenCalledWith(true);
    });

    it('should dispatch events for put requests', () => {
        lastUpdatedResponseInterceptor({ config: { method: 'put' } } as AxiosResponse);

        expect(handleRequestComplete).toHaveBeenCalledWith(true);
    });

    it('should handle configs with capitalized method strings', () => {
        lastUpdatedResponseInterceptor({ config: { method: 'PUT' } } as AxiosResponse);

        expect(handleRequestComplete).toHaveBeenCalledWith(true);
    });

    it('should ignore session refreshes', () => {
        lastUpdatedResponseInterceptor({ config: { method: 'post', url: '/api/session-refresh' } } as AxiosResponse);

        expect(handleRequestComplete).not.toHaveBeenCalled();
    });

    it('should ignore case lock refreshes', () => {
        lastUpdatedResponseInterceptor({
            config: { method: 'post', url: '/api/cases/some-uuid/lock/refresh' },
        } as AxiosResponse);

        expect(handleRequestComplete).not.toHaveBeenCalled();
    });
});

describe('lastUpdatedErrorInterceptor', () => {
    const { handleRequestComplete } = useAppStore();
    const createAxiosError = (config: AxiosRequestConfig) =>
        ({
            config,
            isAxiosError: true,
        }) as AxiosError;
    beforeEach(() => {
        (handleRequestComplete as Mock).mockReset();
    });
    it('should not dispatch events for get requests', () => {
        lastUpdatedErrorInterceptor(createAxiosError({ method: 'get' })).catch(() => {});

        expect(handleRequestComplete).not.toHaveBeenCalled();
    });

    it('should dispatch events for cancelled requests', () => {
        const spy = vi.spyOn(axios, 'isCancel');
        spy.mockImplementationOnce(() => true);
        lastUpdatedErrorInterceptor(new Error('cancelled error')).catch(() => {});

        expect(handleRequestComplete).toHaveBeenCalledWith(false);
    });

    it('should dispatch events for delete requests', () => {
        lastUpdatedErrorInterceptor(createAxiosError({ method: 'delete' })).catch(() => {});

        expect(handleRequestComplete).toHaveBeenCalledWith(false);
    });

    it('should dispatch events for post requests', () => {
        lastUpdatedErrorInterceptor(createAxiosError({ method: 'post' })).catch(() => {});

        expect(handleRequestComplete).toHaveBeenCalledWith(false);
    });

    it('should dispatch events for put requests', () => {
        lastUpdatedErrorInterceptor(createAxiosError({ method: 'put' })).catch(() => {});

        expect(handleRequestComplete).toHaveBeenCalledWith(false);
    });

    it('should handle configs with capitalized method strings', () => {
        lastUpdatedErrorInterceptor(createAxiosError({ method: 'PUT' })).catch(() => {});

        expect(handleRequestComplete).toHaveBeenCalledWith(false);
    });

    it('should ignore session refreshes', () => {
        lastUpdatedErrorInterceptor(createAxiosError({ method: 'post', url: '/api/session-refresh' })).catch(() => {});

        expect(handleRequestComplete).not.toHaveBeenCalled();
    });

    it('should ignore case lock refreshes', () => {
        lastUpdatedErrorInterceptor(
            createAxiosError({ method: 'post', url: '/api/cases/some-uuid/lock/refresh' })
        ).catch(() => {});

        expect(handleRequestComplete).not.toHaveBeenCalled();
    });
});
