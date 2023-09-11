<template>
    <div class="container-xl">
        <!-- Overview with search -->
        <div class="row">
            <div class="col ml-5 mr-5">
                <h1 class="mt-4 mb-4 font-weight-normal d-flex align-items-end">
                    <span class="font-weight-bold">Dossiers zoeken</span>
                </h1>
                <div class="mt-2">
                    <div class="card">
                        <BCardBody class="p-0 pb-3">
                            <ComplianceSearch />
                        </BCardBody>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ComplianceSearch from '../ComplianceSearch/ComplianceSearch.vue';

export default defineComponent({
    name: 'ComplianceOverview',
    components: { ComplianceSearch },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/_variables.scss';

.back-to-overview {
    color: $black;
    font-weight: 500;
}
</style>
