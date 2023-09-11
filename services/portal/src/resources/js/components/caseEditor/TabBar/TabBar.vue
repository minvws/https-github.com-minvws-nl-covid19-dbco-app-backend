<template>
    <div class="tab-bar bg-white sticky-top mr-0 pr-0" ref="tabBar">
        <div class="wrapper px-3 px-xl-5">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-fill"><slot name="left" /></div>
                <div class="text-nowrap"><slot name="right" /></div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'TabBar',
});
</script>
<style lang="scss" scoped>
@import '@/../scss/variables';
.tab-bar {
    border-bottom: 1px solid $lightest-grey;
    z-index: 1021;
    transition: padding-right $sidebar-transition;

    &.sticky {
        box-shadow: $box-shadow-card;
    }

    @media (min-width: 1920px) {
        padding-right: $sidebar-width !important;

        &.sidebar-collapsed {
            padding-right: 0 !important;
        }
    }

    .wrapper {
        max-width: 1920px;
        margin: 0 auto;

        @media (min-width: 1920px) {
            max-width: 1600px;
        }
    }
}
</style>
