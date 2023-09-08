/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import _ from 'lodash';
import axios from 'axios';

window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Cache-Control'] = 'no-store';
window.axios.defaults.headers.common['Pragma'] = 'no-cache';
window.axios.defaults.headers.common['Expires'] = '0';

import BootstrapVue, { ModalPlugin } from 'bootstrap-vue';
import VueMask from 'v-mask';
import Vue from 'vue';
import { Collapse, registerDirectives } from '@dbco/ui-library';
import { defaultParams as BModalDefaults } from './components/modals/BaseModal/BaseModal.vue';
import Modal from './components/plugins/modal';
import i18n from './i18n/index';
import store from './store';

// Directives
import OutsideElementClick from './directives/outsideElementClick';
import SafeHtml from './directives/safeHtml';

// Pages
import AdminPage from './pages/Admin/AdminPage';
import CallcenterPage from './pages/Callcenter/CallcenterPage';
import CallToActionPage from './pages/CallToAction/CallToActionPage';
import CreateCallToActionPage from './pages/CallToAction/CreateCallToActionPage';
import CatalogPage from './pages/Catalog/CatalogPage';
import CaseMetricsPage from './pages/CaseMetricsPage/CaseMetricsPage';
import CovidCaseOverviewPlannerPage from './pages/CovidCaseOverviewPlannerPage/CovidCaseOverviewPlannerPage';
import CovidCaseOverviewUserPage from './pages/CovidCaseOverviewUserPage/CovidCaseOverviewUserPage';
import PagePlacesOverview from './pages/PagePlacesOverview/PagePlacesOverview';
import PlaceEdit from './pages/PlaceEdit/PlaceEdit';
import ConversationCoachPage from './pages/Supervision/ConversationCoachPage';
import MedicalSupervisorPage from './pages/Supervision/MedicalSupervisorPage';
import PlaygroundPage from './pages/Playground/PlaygroundPage';

import VueRouter from 'vue-router';
import FiltersPlugin from './plugins/filters';
import TypingHelpers from './plugins/typings';
import router from './router/router';

import VueCookies from 'vue-cookies';
import InactivityTimer from './InactivityTimer';

// Forms
import './useForms';

import { createPinia, PiniaVuePlugin } from 'pinia';
import { createAxiosInstance } from '@dbco/portal-api/defaults';

import {
    lastUpdatedRequestInterceptor,
    lastUpdatedResponseInterceptor,
    lastUpdatedErrorInterceptor,
} from '@/interceptors/lastUpdatedInterceptor';
import { errorInterceptor } from '@/interceptors/errorInterceptor';

import CovidCaseEdit from './components/caseEditor/CovidCaseEdit/CovidCaseEdit.vue';
import ComplianceOverview from './components/compliance/ComplianceOverview/ComplianceOverview';
import ComplianceSearchResults from './components/compliance/ComplianceSearchResults/ComplianceSearchResults';
import DbcoHeader from './components/utils/DbcoHeader/DbcoHeader';
import DbcoUserInfo from './components/utils/DbcoUserInfo/DbcoUserInfo';
import DbcoVersion from './components/utils/DbcoVersion/DbcoVersion';
import DbcoErrorAlert from '@/components/utils/DbcoErrorAlert/DbcoErrorAlert';
import DbcoTimeoutAlert from '@/components/utils/DbcoTimeoutAlert/DbcoTimeoutAlert';

createAxiosInstance({ timeout: 10000 }, (instance) => {
    instance.interceptors.request.use(lastUpdatedRequestInterceptor);
    instance.interceptors.response.use(undefined, errorInterceptor);
    instance.interceptors.response.use(lastUpdatedResponseInterceptor, lastUpdatedErrorInterceptor);
    return instance;
});

Vue.use(BootstrapVue, {
    formControls: {
        autocomplete: 'off',
    },
    BModal: BModalDefaults,
});
Vue.use(ModalPlugin);
Vue.use(Modal);
Vue.use(VueMask);
Vue.use(VueRouter);
Vue.use(PiniaVuePlugin);
Vue.use(FiltersPlugin);
Vue.use(TypingHelpers);

Vue.use(VueCookies);
Vue.use(InactivityTimer);

// Components that are used directly from a blade template must be registered here
Vue.component('covid-case-edit', CovidCaseEdit);
Vue.component('collapse', Collapse);
Vue.component('compliance-overview', ComplianceOverview);
Vue.component('compliance-search-results', ComplianceSearchResults);
Vue.component('dbco-header', DbcoHeader);

Vue.component('dbco-user-info', DbcoUserInfo);
Vue.component('dbco-version', DbcoVersion);
Vue.component('dbco-error-alert', DbcoErrorAlert);
Vue.component('dbco-timeout-alert', DbcoTimeoutAlert);

Vue.component('page-covid-case-overview-planner', CovidCaseOverviewPlannerPage);
Vue.component('page-covid-case-overview-user', CovidCaseOverviewUserPage);
Vue.component('page-places-overview', PagePlacesOverview);
Vue.component('page-catalog', CatalogPage);

Vue.component('page-medical-supervisor', MedicalSupervisorPage);
Vue.component('page-conversation-coach', ConversationCoachPage);

Vue.component('page-call-to-action', CallToActionPage);
Vue.component('create-call-to-action', CreateCallToActionPage);

Vue.component('page-callcenter', CallcenterPage);

Vue.component('place-edit', PlaceEdit);

Vue.component('page-case-metrics', CaseMetricsPage);
Vue.component('page-playground', PlaygroundPage);

Vue.component('page-admin', AdminPage);

// Directives
Vue.directive('click-outside', OutsideElementClick);
Vue.directive('safe-html', SafeHtml);

registerDirectives(Vue);

const pinia = createPinia();

window.app = new Vue({
    el: '#app',
    store,
    i18n,
    router,
    pinia,
});
import.meta.glob(['../images/**', '../fonts/**', '../img/**']);
