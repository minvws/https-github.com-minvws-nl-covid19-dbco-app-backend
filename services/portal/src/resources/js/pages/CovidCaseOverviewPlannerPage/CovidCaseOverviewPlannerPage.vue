<template>
    <div>
        <div
            class="bg-white title-bar"
            v-if="organisation && organisation.hasOutsourceToggle && isOutsourcingToRegionalGGDEnabled"
        >
            <div class="container-xl h-100">
                <div class="row">
                    <div class="col ml-5 mr-5">
                        <div class="d-flex justify-content-between align-items-center h-100">
                            <div></div>
                            <div>
                                <BForm inline>
                                    <label class="mr-2" for="outsource-switch"
                                        >Beschikbaar voor cases andere GGD's</label
                                    >
                                    <BFormCheckbox
                                        data-testid="outsource-switch"
                                        switch
                                        id="outsource-switch"
                                        size="lg"
                                        v-model="organisation.isAvailableForOutsourcing"
                                        @change="updateIsAvailableForOutsourcing"
                                    />
                                </BForm>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <router-view></router-view>
    </div>
</template>

<script lang="ts">
import { userApi } from '@dbco/portal-api';
import env from '@/env';
import { mapRootGetters } from '@/utils/vuex';
import { defineComponent } from 'vue';

export default defineComponent({
    components: {},
    name: 'CovidCaseOverviewPlannerPage',
    data() {
        return {
            topDistance: 0,
        };
    },
    computed: {
        ...mapRootGetters({ organisation: 'userInfo/organisation' }),
        isOutsourcingToRegionalGGDEnabled() {
            return env.isOutsourcingEnabled && env.isOutsourcingToRegionalGGDEnabled;
        },
    },
    methods: {
        async updateIsAvailableForOutsourcing(value: boolean) {
            const currentOrganisation = await userApi.updateOrganisation({ isAvailableForOutsourcing: value });
            await this.$store.dispatch('userInfo/CHANGE', { path: 'organisation', values: currentOrganisation });
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.title-bar {
    height: 60px;
    padding: 8px 0;
    margin-bottom: 2rem;

    ::v-deep {
        form {
            color: $black;
            padding: 10px;
            box-shadow: inset 0px 0px 0px 1px $lightest-grey;
            border-radius: $border-radius-small;

            .custom-switch {
                margin-top: -3px;
            }
        }
    }
}

h2 .title {
    max-width: 400px;
}
</style>
