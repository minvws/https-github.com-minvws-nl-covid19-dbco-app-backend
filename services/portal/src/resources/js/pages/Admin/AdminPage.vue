<template>
    <div>
        <router-view v-if="hasAdminPolicyAdviceModule" />
        <div v-else class="tw-flex tw-h-96 tw-justify-center tw-items-center">Geen beheer module gevonden</div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { PermissionV1 } from '@dbco/enum';
import { mapRootGetters } from '@/utils/vuex';
import { RouterTab, TabList } from '@dbco/ui-library';

export default defineComponent({
    components: { TabList, RouterTab },
    data() {
        return { PermissionV1 };
    },
    computed: {
        ...mapRootGetters({ hasPermission: 'userInfo/hasPermission' }),
        hasAdminPolicyAdviceModule() {
            return this.hasPermission(PermissionV1.VALUE_adminPolicyAdviceModule);
        },
    },
    async created() {
        if (this.$route.path === '/beheren' && this.hasAdminPolicyAdviceModule) {
            await this.$router.push('/beheren/beleidsversies');
        }
    },
});
</script>
