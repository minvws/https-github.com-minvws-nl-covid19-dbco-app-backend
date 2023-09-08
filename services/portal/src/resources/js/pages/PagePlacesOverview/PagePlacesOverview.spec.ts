import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import PagePlacesOverview from './PagePlacesOverview.vue';

import BulkActionBar from '@/components/utils/BulkActionBar/BulkActionBar.vue';
import organisationStore from '@/store/organisation/organisationStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { PermissionV1 } from '@dbco/enum';
import { setupTest } from '@/utils/test';
import i18n from '@/i18n/index';
import type { VueConstructor } from 'vue';
import router from '@/router/router';
import BootstrapVue from 'bootstrap-vue';
import VueRouter from 'vue-router';
import TypingHelpers from '@/plugins/typings';
import { registerDirectives } from '@dbco/ui-library';

vi.mock('@dbco/portal-api/client/requireActualplace.api', () => ({
    verifyMulti: vi.fn(() => Promise.resolve()),
}));

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(VueRouter);
localVue.use(TypingHelpers);
registerDirectives(localVue);

const createComponent = setupTest((_: VueConstructor, userInfoState: object = {}) => {
    const userInfoStoreModule = {
        ...userInfoStore,
        state: {
            ...userInfoStore.state,
            ...userInfoState,
            organisation: {
                name: 'GGD 1',
            },
        },
    };

    const organisationStoreModule = {
        ...organisationStore,
        state: {
            ...organisationStore.state,
        },
    };

    return mount(PagePlacesOverview, {
        localVue,
        i18n,
        router,
        stubs: {
            ['router-view']: true,
        },
        store: new Vuex.Store({
            modules: {
                userInfo: userInfoStoreModule,
                organisation: organisationStoreModule,
            },
        }),
    });
});

describe('PagePlacesOverview.vue', () => {
    beforeAll(async () => {
        await router.push('/places/full');
    });

    it('should load', () => {
        // ARRANGE
        const wrapper = createComponent();

        // ASSERT
        expect(wrapper.find('div').exists()).toBe(true);
    });

    it('should hide BulkActionBar if selected.length === 0', () => {
        // ARRANGE
        const wrapper = createComponent();

        // ASSERT
        expect(wrapper.findComponent(BulkActionBar).exists()).toBe(false);
    });

    it('should show BulkActionBar if selected.length > 0', async () => {
        // ARRANGE
        const wrapper = createComponent();

        // ACT
        await wrapper.setData({
            selected: [{}, {}],
        });

        // ASSERT
        expect(wrapper.findComponent(BulkActionBar).exists()).toBe(true);
    });

    it('should show BulkActionBar with text "2 contexten" if selected.length == 2"', async () => {
        // ARRANGE
        const wrapper = createComponent();

        // ACT
        await wrapper.setData({
            selected: [{}, {}],
        });

        // ASSERT
        expect(wrapper.findComponent(BulkActionBar).props().text).toBe('2 contexten');
    });

    it('should show BulkActionBar with text "1 context" if selected.length == 1"', async () => {
        // ARRANGE
        const wrapper = createComponent();

        // ACT
        await wrapper.setData({
            selected: [{}],
        });

        // ASSERT
        expect(wrapper.findComponent(BulkActionBar).props().text).toBe('1 context');
    });

    it('should update "data.selected" when methods.updatedSelectedPlaces() is being called', async () => {
        // ARRANGE
        const wrapper = createComponent();

        // ACT
        await wrapper.setData({
            selected: [{}],
        });

        const newPlaces = [{}, {}];
        wrapper.vm.updateSelectedPlaces(newPlaces);

        // ASSERT
        expect(wrapper.vm.selected.length).toBe(2);
    });

    it('should empty "data.selected" when methods.emptySelected() is being called', () => {
        // ARRANGE
        const wrapper = createComponent();

        wrapper.vm.$refs.placesOverviewTable.emptySelected = vi.fn();
        const spyFormopen = wrapper.vm.$refs.placesOverviewTable.emptySelected;

        wrapper.vm.emptySelected();

        // ASSERT
        expect(spyFormopen).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.selected.length).toBe(0);
    });

    it('verify button should be visible in BulkActionBar', async () => {
        // ARRANGE
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeVerify],
        });

        // ACT
        await wrapper.setData({
            selected: [{}],
        });

        const VerifyButton = wrapper.findComponent(BulkActionBar).find('[data-testid="verify-places"]');

        expect(VerifyButton.exists()).toBeTruthy();
    });

    it('merge button should be visible in BulkActionBar', async () => {
        // ARRANGE
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeMerge],
        });

        // ACT
        await wrapper.setData({
            selected: [{}],
        });

        const VerifyButton = wrapper.findComponent(BulkActionBar).find('[data-testid="merge-places"]');

        expect(VerifyButton.exists()).toBeTruthy();
    });
});
