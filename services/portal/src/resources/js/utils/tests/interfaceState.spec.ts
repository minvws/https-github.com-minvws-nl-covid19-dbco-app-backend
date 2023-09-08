import store from '@/store';
import { userCanEdit, hasCaseLock } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { isEditCaseModulePath } from '@/utils/url';

vi.mock('@/utils/url');

describe('userCanEdit', () => {
    it('Should return false if user doesnt have correct permission', async () => {
        (isEditCaseModulePath as Mock).mockImplementationOnce(() => true);
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { userCanEdit: false },
        });

        expect(userCanEdit()).toBe(false);
    });

    it('Should return true if user has correct permission', async () => {
        (isEditCaseModulePath as Mock).mockImplementationOnce(() => true);
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { userCanEdit: true },
        });

        expect(userCanEdit()).toBe(true);
    });

    it('Should return false if permission unknown', async () => {
        (isEditCaseModulePath as Mock).mockImplementationOnce(() => true);
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { userCanEdit: undefined },
        });
        expect(userCanEdit()).toBe(false);
    });

    it('Should return true if case is locked', async () => {
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { isLocked: true },
        });
        expect(hasCaseLock()).toBe(true);
    });

    it('Should return false if case is locked', async () => {
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { isLocked: false },
        });
        expect(hasCaseLock()).toBe(false);
    });
});
