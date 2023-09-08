import env from '@/env';
import type { Component } from 'vue';
import { getCurrentInstance } from 'vue';
import type { RouteConfig } from 'vue-router';
import VueRouter from 'vue-router';
import AdminModule from '../components/admin/AdminModule/AdminModule.vue';
import CalendarViewDetail from '../components/admin/PolicyAdvice/CalendarViewDetail/CalendarViewDetail.vue';
import PolicyVersionDetail from '../components/admin/PolicyAdvice/PolicyVersionDetail/PolicyVersionDetail.vue';
import PolicyGuidelineDetail from '../components/admin/PolicyAdvice/PolicyGuidelineDetail/PolicyGuidelineDetail.vue';
import PolicyVersionTable from '../components/admin/PolicyAdvice/PolicyVersionTable/PolicyVersionTable.vue';
import PlacesOverviewTable from '../components/contextManager/PlacesOverviewTable/PlacesOverviewTable.vue';
import CovidCaseOverviewPlannerView from '../pages/CovidCaseOverviewPlannerPage/CovidCaseOverviewPlannerView.vue';

export const routes: RouteConfig[] = [
    { path: '/planner/:list?', component: CovidCaseOverviewPlannerView },
    {
        path: '/places/:view?',
        component: PlacesOverviewTable,
        props: (route) => ({ listType: route.params.view }),
    },
    {
        path: '/beheren/beleidsversies',
        component: AdminModule as Component,
        children: [
            { path: '', component: PolicyVersionTable as Component },
            {
                path: ':versionUuid',
                name: 'policyVersion',
                component: PolicyVersionDetail as Component,
            },
            {
                path: ':versionUuid/kalender-views/:viewUuid',
                name: 'calendarView',
                component: CalendarViewDetail as Component,
            },
            {
                path: ':versionUuid/richtlijnen/:policyGuidelineUuid',
                name: 'policyGuideline',
                component: PolicyGuidelineDetail as Component,
            },
        ],
        beforeEnter: (to, from, next) => (env.isAdminViewEnabled ? next() : next('/')),
    },
];

export const router = new VueRouter({
    routes,
    mode: 'history',
});

/**
 * These hooks are loosely based on the useRoute hook from vue-router.
 * @see: /node_modules/vue-router/composables.js
 */
function useRoot() {
    const root = getCurrentInstance()?.proxy.$root || window.app;
    if (!root) throw Error('Root not available');
    return root;
}

export const useRoute = () => useRoot().$route;
export const useRouter = () => useRoot().$router;

export default router;
