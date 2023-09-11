<template>
    <div>
        <h1 class="sr-only">Case bewerken</h1>
        <template v-if="loaded">
            <template v-if="hasUserEditPermission && hasCaseLock">
                <FormInfo
                    v-if="removed"
                    class="info-block--lg"
                    :text="$tc('components.caseUnlockNotification.title')"
                    infoType="success"
                    :hasAction="true"
                    :actionText="$tc('components.caseUnlockNotification.action')"
                    @actionTriggered="reloadCase()"
                />
                <FormInfo v-else class="info-block--lg" :text="translatedCaseLockNotification" infoType="warning" />
            </template>
            <InfoBar @height="handleInfoBarHeight" />

            <CovidCaseHeaderBar :covidCase="covidCase" :class="{ 'sidebar-collapsed': isSidebarCollapsed }" />

            <TabBar
                :class="{ 'sidebar-collapsed': isSidebarCollapsed, sticky }"
                ref="tabBar"
                :style="{ top: `${infoBarHeight}px` }"
            >
                <template v-slot:left>
                    <BTabs v-model="activeTab" ref="tabs" nav-class="nav-tabs--borderless w-100" id="navtabs">
                        <template #tabs-start>
                            <BTab
                                v-for="tab in $as.defined(schema).tabs"
                                :key="tab.id"
                                :id="tab.id"
                                :title="tab.title"
                            />
                        </template>
                    </BTabs>
                </template>
                <template v-slot:right>
                    <LastUpdated v-show="sticky" />
                </template>
            </TabBar>

            <div class="d-flex">
                <div class="w-100">
                    <CaseUpdateInfoBar v-if="isIntakeMatchCaseEnabled" />
                    <BTabs v-model="activeTab" class="flex-fill mt-5" nav-class="d-none" lazy no-fade>
                        <BTab v-for="tab in $as.defined(schema).tabs" :key="tab.id" title-item-class="d-none">
                            <DbcoFormWrap>
                                <FormRenderer :rules="$as.defined(schema).rules.index" :schema="tab.schema()" />
                            </DbcoFormWrap>
                        </BTab>
                    </BTabs>
                    <div class="ml-5 pl-2">
                        <DbcoVersion />
                    </div>
                </div>
                <CovidCaseSidebar
                    :schema="$as.defined(schema).sidebar()"
                    @collapsed="(val) => (isSidebarCollapsed = val)"
                />
            </div>
        </template>
        <SupervisionModal />
        <OsirisModal :covid-case="covidCase" />
        <ContactsEditingModal />
        <ContextsEditingModal />
    </div>
</template>

<script lang="ts">
import type Vue from 'vue';
import { defineComponent } from 'vue';
import CovidCaseSidebar from '../CovidCaseSidebar/CovidCaseSidebar.vue';
import ContactsEditingModal from '@/components/modals/ContactsEditingModal/ContactsEditingModal.vue';
import ContextsEditingModal from '@/components/modals/ContextsEditingModal/ContextsEditingModal.vue';
import SupervisionModal from '@/components/modals/SupervisionModal/SupervisionModal.vue';
import CovidCaseHistory from '@/components/utils/CovidCaseHistory/CovidCaseHistory.vue';
import CovidCaseHeaderBar from '../CovidCaseHeaderBar/CovidCaseHeaderBar.vue';
import DbcoFormWrap from '@/components/utils/DbcoFormWrap/DbcoFormWrap.vue';
import DbcoVersion from '@/components/utils/DbcoVersion/DbcoVersion.vue';
import CaseUpdateInfoBar from '../CaseUpdateInfoBar/CaseUpdateInfoBar.vue';
import OsirisModal from '@/components/osiris/OsirisModal/OsirisModal.vue';
import InfoBar from '../InfoBar/InfoBar.vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import TabBar from '../TabBar/TabBar.vue';
import LastUpdated from '../LastUpdated/LastUpdated.vue';
import { getRootSchema } from '@/components/form/ts/formSchema';
import env from '@/env';
import type { Schema } from '@/components/form/ts/schemaType';
import { useCaseLockStore } from '@/store/caseLock/caseLockStore';
import { mapActions } from 'pinia';
import { hasCaseLock } from '@/utils/interfaceState';
import type { SafeHtml } from '@/utils/safeHtml';
import { StoreType } from '@/store/storeType';
import { SharedActions } from '@/store/actions';
import { PermissionV1 } from '@dbco/enum';

export default defineComponent({
    name: 'CovidCaseEdit',
    components: {
        CovidCaseSidebar,
        ContactsEditingModal,
        ContextsEditingModal,
        SupervisionModal,
        CovidCaseHistory,
        DbcoFormWrap,
        DbcoVersion,
        FormInfo,
        OsirisModal,
        InfoBar,
        CaseUpdateInfoBar,
        LastUpdated,
        TabBar,
        CovidCaseHeaderBar,
    },
    data() {
        return {
            sticky: false,
            activeTab: 0,
            infoBarHeight: 0,
            isSidebarCollapsed: false,
            loaded: false,
            schema: null as Schema | null,
            scrollToTopHeight: 0,
        };
    },
    props: {
        caseUuid: {
            type: String,
            required: true,
        },
    },
    computed: {
        covidCase: {
            get() {
                return this.$store.getters[`${StoreType.INDEX}/forms`];
            },
            async set(payload: any) {
                await this.$store.dispatch(`${StoreType.INDEX}/${SharedActions.CHANGE}`, payload);
            },
        },
        hasUserEditPermission() {
            return this.$store.getters[`${StoreType.USERINFO}/hasPermission`](PermissionV1.VALUE_caseUserEdit);
        },
        isIntakeMatchCaseEnabled() {
            return env.isIntakeMatchCaseEnabled;
        },
        removed() {
            return useCaseLockStore().caseLock.removed;
        },
        translatedCaseLockNotification(): SafeHtml {
            return useCaseLockStore().translatedCaseLockNotification;
        },
        hasCaseLock,
    },
    async created() {
        this.covidCase = { path: 'uuid', values: this.caseUuid };
        await this.getData(this.caseUuid);
        await this.initialize(this.caseUuid);
        this.scrollToTopHeight = ((this.$refs.tabBar as Vue).$el as HTMLDivElement).getBoundingClientRect().top;
    },
    mounted() {
        window.addEventListener('scroll', this.scroll, true);
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.scroll, true);
        this.stopPolling();
    },
    methods: {
        ...mapActions(useCaseLockStore, ['initialize', 'stopPolling']),
        reloadCase() {
            window.location.reload();
        },
        handleInfoBarHeight(value: number) {
            this.infoBarHeight = value;
        },
        scroll() {
            this.sticky = ((this.$refs.tabBar as Vue).$el as HTMLDivElement).getBoundingClientRect().top === 0;
        },
        setTabByHash() {
            // Set active tab
            const hash = window.location.href.split('#')[1];
            if (!hash) return;

            const tabIndex = this.schema?.tabs.findIndex((tab) => tab.id === hash);
            if (tabIndex === -1 || tabIndex === undefined) return;

            this.activeTab = tabIndex;
        },
        async getData(uuid: string) {
            this.covidCase = { path: 'uuid', values: uuid };

            await this.$store.dispatch(`${StoreType.INDEX}/${SharedActions.LOAD}`, uuid);
            this.schema = getRootSchema();
            this.setTabByHash();

            this.loaded = true;
        },
    },
    watch: {
        activeTab(index) {
            const hash = this.schema?.tabs[index].id;
            const newUrl = window.location.href.split('#')[0] + `#${hash}`;
            window.history.replaceState(null, '', newUrl);
            setTimeout(() => {
                window.scrollTo({ top: this.scrollToTopHeight, behavior: 'smooth' });
            }, 300);
        },
    },
});
</script>
