<template>
    <header>
        <div
            ref="content"
            :class="{ 'tw-fixed tw-top-0 tw-left-0 tw-right-0 tw-z-[1030]': isFixed }"
            data-testid="header-content"
        >
            <dbco-environment-banner :environment="environment"></dbco-environment-banner>
            <dbco-nav-bar :section="section"></dbco-nav-bar>
        </div>
        <div
            v-if="isFixed"
            class="tw-w-full"
            :style="{ height: contentHeight + 'px' }"
            data-testid="header-placeholder"
        ></div>
    </header>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { computed, defineComponent, onBeforeUnmount, onMounted, ref } from 'vue';
import type { Environment } from '@/components/utils/DbcoEnvironmentBanner/DbcoEnvironmentBanner.vue';
import DbcoEnvironmentBanner from '@/components/utils/DbcoEnvironmentBanner/DbcoEnvironmentBanner.vue';
import DbcoNavBar from '@/components/utils/DbcoNavBar/DbcoNavBar.vue';

export default defineComponent({
    props: {
        environment: { type: String as PropType<`${Environment}`>, required: true },
        section: { type: String, required: true },
    },
    components: {
        DbcoEnvironmentBanner,
        DbcoNavBar,
    },
    setup({ section }) {
        const content = ref<HTMLElement | null>(null);
        const contentHeight = ref(0);
        const isFixed = computed(() => !['editcase', 'editplace'].includes(section));

        function resize() {
            contentHeight.value = content.value?.clientHeight || 0;
        }

        onMounted(() => {
            window.addEventListener('resize', resize);
            resize();
        });

        onBeforeUnmount(() => {
            window.removeEventListener('resize', resize);
        });

        return { isFixed, content, contentHeight };
    },
});
</script>
