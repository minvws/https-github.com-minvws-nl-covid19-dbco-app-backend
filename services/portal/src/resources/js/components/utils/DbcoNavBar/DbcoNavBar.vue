<template>
    <BNavbar id="header" toggleable="lg" type="dark" class="navbar-expand navbar-custom">
        <BNavbarNav class="navbar-nav__section w-100" data-testid="navigation" ref="navigation">
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_caseListUserCases)"
                href="/cases"
                :active="section === 'editcase' || section === 'cases' || section === ''"
                ><i class="icon icon--cases"></i>Mijn cases</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_caseViewCallToAction)"
                href="/taken"
                :active="section === 'taken'"
                ><i class="icon icon--tasks"></i>Taken</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_caseListPlannerCases)"
                href="/planner"
                :active="section === 'planner'"
                ><i class="icon icon--planner"></i>Werkverdeling</BNavItem
            >
            <BNavItem v-if="hasPermission(PermissionV1.VALUE_placeList)" href="/places" :active="section === 'places'"
                ><i class="icon icon--places"></i>Contexten</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_callcenterView)"
                href="/dossierzoeken"
                :active="section === 'callcenter'"
                ><i class="icon icon--search-cases"></i>Dossier zoeken</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_expertQuestionConversationCoach)"
                href="/gesprekscoach"
                :active="section === 'gesprekscoach'"
                ><i class="icon icon--coach"></i>Gesprekscoach</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_expertQuestionMedicalSupervisor)"
                href="/medische-supervisie"
                :active="section === 'medische-supervisie'"
                ><i class="icon icon--medical-supervision"></i>Medische supervisie</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_caseMetricsList) && env.isCaseMetricsEnabled"
                href="/casemetrics"
                :active="section === 'casemetrics'"
                ><i class="icon icon--steer"></i>Stuurinformatie</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_caseListAccessRequests)"
                href="/compliance"
                :active="section === 'compliance'"
                ><i class="icon icon--compliance"></i>Compliance</BNavItem
            >
            <BNavItem
                v-if="hasPermission(PermissionV1.VALUE_adminView) && env.isAdminViewEnabled"
                href="/beheren"
                :active="section === 'beheren'"
                ><Icon name="plus" class="tw-mr-2.5" />Beheren</BNavItem
            >

            <BNavItemDropdown data-testid="dropdown" lazy right text="Meer" />
        </BNavbarNav>

        <BNavbarNav class="ml-auto">
            <DbcoMultilevelDropdown
                data-testid="profile-dropdown"
                class="profile-dropdown"
                right
                toggleClass="nav-link"
                :options="profileDropdownOptions"
            >
                <template v-slot:profile>
                    <!-- Profile button content -->
                    <span class="w-100 d-flex justify-content-between">
                        <span class="d-flex flex-column">
                            <span class="font-weight-bold text-nowrap">{{ organisation && organisation.name }}</span>
                            <span>{{ user && user.name }}</span>
                        </span>
                        <span
                            v-if="hasPermission(PermissionV1.VALUE_organisationUpdate) && organisation"
                            class="ml-2"
                            id="navbar-phase-badge"
                        >
                            <DbcoPhaseBadge :bcoPhase="$as.defined(organisation.bcoPhase)" tooltipPlacement="left" />
                        </span>
                    </span>
                </template>
                <!-- Dropdown button content -->
                <i class="icon icon--profile icon--md my-0"></i>
            </DbcoMultilevelDropdown>
        </BNavbarNav>
    </BNavbar>
</template>

<script lang="ts">
import { userApi } from '@dbco/portal-api';
import env from '@/env';
import type { DropdownOption } from '@/components/formControls/DbcoMultilevelDropdown/DbcoMultilevelDropdown.vue';
import {
    default as DbcoMultilevelDropdown,
    DropdownOptionType,
} from '@/components/formControls/DbcoMultilevelDropdown/DbcoMultilevelDropdown.vue';
import { defineComponent } from 'vue';
import type { BcoPhaseV1 } from '@dbco/enum';
import { PermissionV1, bcoPhaseV1Options } from '@dbco/enum';
import DbcoPhaseBadge from '../DbcoPhaseBadge/DbcoPhaseBadge.vue';
import { mapRootGetters } from '@/utils/vuex';
import { Icon } from '@dbco/ui-library';

interface SizedMenuItem {
    active: boolean;
    element: HTMLLIElement;
    width: number;
}

export default defineComponent({
    name: 'DbcoNavBar',
    components: {
        DbcoMultilevelDropdown,
        DbcoPhaseBadge,
        Icon,
    },
    props: {
        section: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            env,
            menuItems: [] as SizedMenuItem[],
            PermissionV1,
        };
    },
    async mounted() {
        await this.$nextTick();
        this.initNavbar();
    },
    destroyed() {
        window.removeEventListener('resize', this.resize);
    },
    computed: {
        ...mapRootGetters({
            user: 'userInfo/user',
            organisation: 'userInfo/organisation',
            hasPermission: 'userInfo/hasPermission',
        }),
        profileDropdownOptions() {
            const options: DropdownOption[] = [
                {
                    type: DropdownOptionType.ITEM,
                    slot: 'profile',
                    label: this.user?.name || '',
                    href: '/profile',
                },
            ];
            if (this.hasPermission(PermissionV1.VALUE_organisationUpdate) && this.organisation) {
                // Phase dropdown to select different phase
                options.push({
                    type: DropdownOptionType.MENU,
                    label: this.organisation.bcoPhase
                        ? bcoPhaseV1Options[this.organisation.bcoPhase]
                        : bcoPhaseV1Options.none,
                    options: Object.entries(bcoPhaseV1Options)
                        .sort(([phaseA], [phaseB]) => (phaseA < phaseB ? -1 : 1))
                        .map(([phase, label]) => ({
                            type: DropdownOptionType.ITEM,
                            label,
                            isSelected: phase === this.organisation!.bcoPhase,
                            onClick: () => {
                                this.onChangePhase(phase);
                            },
                        })),
                });
            }

            options.push({
                type: DropdownOptionType.ITEM,
                label: 'Privacyverklaring',
                onClick: () => {
                    window.open('/consent/privacy');
                },
            });
            options.push({
                type: DropdownOptionType.ITEM,
                label: 'Logout',
                onClick: () => {
                    window.stop();
                    window.location.replace('/logout');
                },
            });

            return options;
        },
        meta: {
            get() {
                return this.$store.getters['index/meta'];
            },
            async set(meta: any) {
                await this.$store.dispatch('index/CHANGE', { path: 'meta', values: meta });
            },
        },
    },
    methods: {
        initNavbar() {
            const navigationEl = this.$refs.navigation as HTMLElement;

            const menuItems = Array.from(
                navigationEl.querySelectorAll<HTMLLIElement>('li.nav-item:not(.b-nav-dropdown)')
            );

            this.menuItems = menuItems.map((element) => ({
                active: element.querySelector('.nav-link.active') !== null,
                element,
                width: element.clientWidth,
            }));

            window.addEventListener('resize', this.resize);
            this.resize();
        },
        onChangePhase(phase: string) {
            this.$modal.show({
                title: 'Weet je zeker dat je de fase wilt veranderen?',
                text: `Je verandert zo de fase naar ${bcoPhaseV1Options[phase as BcoPhaseV1]}.`,
                cancelTitle: 'Nee, terug',
                okTitle: 'Ja, verander',
                onConfirm: async () => {
                    const currentOrganisation = await userApi.updateOrganisation({ bcoPhase: phase });
                    await this.$store.dispatch('userInfo/CHANGE', {
                        path: 'organisation',
                        values: currentOrganisation,
                    });
                    if (this.meta?.organisation) {
                        this.meta = {
                            ...this.meta,
                            organisation: {
                                ...this.meta.organisation,
                                bcoPhase: currentOrganisation.bcoPhase,
                            },
                        };
                    }
                },
            });
        },
        resize() {
            const navigationEl = this.$refs.navigation as HTMLElement;

            const dropdownEl = navigationEl.querySelector<HTMLLIElement>('li.b-nav-dropdown');
            const dropdownMenuEl = dropdownEl?.querySelector<HTMLUListElement>('ul.dropdown-menu');
            if (!dropdownEl || !dropdownMenuEl) return;

            // Get dropdown width while visible
            const dropdownWidth = dropdownEl.clientWidth;

            // Clear out elements from the dropdown menu to get the right navigation width
            dropdownMenuEl.innerHTML = '';
            navigationEl.innerHTML = '';

            // Get navigation width without items
            const navigationWidth = navigationEl.clientWidth - dropdownWidth;

            // Starting width is at least the active item
            let accWidth = this.menuItems.find((item) => item.active)?.width || 0;

            // Add all items until the navigation width is reached
            const menuItemsOnScreen = this.menuItems.filter(
                (item) => item.active || (accWidth += item.width) <= navigationWidth
            );

            // Add remeaning items to the dropdown menu
            const menuItemsInDropdown = this.menuItems.filter((item) => !menuItemsOnScreen.includes(item));

            // Append items to navbar
            menuItemsOnScreen.forEach((item) => {
                navigationEl.appendChild(item.element);
            });

            // Append items to dropdownb
            menuItemsInDropdown.forEach((item) => {
                dropdownMenuEl.appendChild(item.element);
            });

            // Append dropdown to navbar
            navigationEl.appendChild(dropdownEl);

            // Only show dropdown if there are items in it
            dropdownEl.style.display = menuItemsInDropdown.length > 0 ? 'block' : 'none';
        },
    },
});
</script>
