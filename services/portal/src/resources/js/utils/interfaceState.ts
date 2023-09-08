import store from '@/store';
import { StoreType } from '@/store/storeType';
import type { IndexStoreState } from '@/store/index/indexStore';
import { isEditCaseModulePath } from './url';

export const userCanEdit = () => {
    const meta: IndexStoreState['meta'] = store.getters[`${StoreType.INDEX}/meta`];

    return !!meta.userCanEdit || !isEditCaseModulePath();
};

export const hasCaseLock = () => {
    const meta: IndexStoreState['meta'] = store.getters[`${StoreType.INDEX}/meta`];

    return !!meta.isLocked;
};
