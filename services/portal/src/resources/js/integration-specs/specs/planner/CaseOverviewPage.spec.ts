import store from '@/store';
import { render, fireEvent, within } from '@testing-library/vue';
import Page from '@/pages/CovidCaseOverviewPlannerPage/CovidCaseOverviewPlannerPage.vue';
import BootstrapVue from 'bootstrap-vue';
import { createLocalVue } from '@vue/test-utils';
import InfiniteLoading from '../../stubs/infinite-loading.mock.vue';
import '@/useForms';
import { withLayout } from '@/integration-specs/utils/with-layout';
import { enableMockServer } from '@/integration-specs/utils/msw-init';
import { routes } from '@/router/router';
import type { RawLocation } from 'vue-router';
import VueRouter from 'vue-router';
import i18n from '@/i18n';
import { PiniaVuePlugin } from 'pinia';
import { createTestingPinia } from '@pinia/testing';
import FiltersPlugin from '@/plugins/filters';

// makes sure a empty axios client is used without interceptors
vi.mock('@dbco/portal-api/defaults', async () => {
    const axios = await import('axios');
    return {
        getAxiosInstance: () => axios,
    };
});

enableMockServer();
const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(VueRouter);
localVue.use(PiniaVuePlugin);
localVue.use(FiltersPlugin);

const visitPage = (location: RawLocation = { path: '/planner' }) =>
    render(
        withLayout(Page),
        {
            store: store,
            stubs: { InfiniteLoading },
            localVue,
            i18n,
            routes: new VueRouter({ routes, mode: 'abstract' }),
            pinia: createTestingPinia({
                stubActions: false,
            }),
        },
        async (vue, store, $router) => {
            await $router.push(location);
        }
    );
describe.skip('Planner cases overview page', () => {
    it('Should show dropdown with caselists on planner page', async () => {
        const { findByRole, getByRole, findByText } = visitPage();
        // check of table has rows with cases
        await findByText(/AB1-555-123/i);

        await fireEvent.click(
            getByRole('tab', {
                name: /Toegewezen/i,
            })
        );
        // wait for 'medewekers' filter to become visible (indicates all initial data is loaded)
        await findByRole('button', {
            name: /alle medewerkers/i,
        });

        // click case list dropdown
        await fireEvent.click(
            await findByRole('button', {
                name: /lijsten/i,
            })
        );

        // should have an other list in dropdown
        expect(within(await findByRole('menu', { name: /Lijsten/i })).queryByText(/test lijst/i)).toBeTruthy();
    });
});
