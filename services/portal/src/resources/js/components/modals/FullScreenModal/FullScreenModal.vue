<template>
    <div class="full-screen-modal" v-if="value" ref="modal">
        <InfoBar @height="(val) => (infoBarHeight = val)" />
        <button class="top-header-action mt-4" @click="closeModal" type="button">
            <slot name="header" />
        </button>
        <div class="header-bar sticky-top" :class="{ stuck: scrolledPast }" :style="{ top: `${infoBarHeight}px` }">
            <h2 class="tw-flex tw-items-center tw-gap-1 tw-mb-0">
                <span v-for="(item, index) in path" :key="item">
                    <span v-if="index !== path.length - 1">{{ item }} / </span>
                    <strong v-else>{{ item }}</strong>
                </span>
                <slot name="title" />
            </h2>
            <slot name="action" />
        </div>
        <div class="content">
            <div class="main"><slot /></div>
            <div><slot name="sidebar" /></div>
        </div>
    </div>
</template>

<script>
import InfoBar from '@/components/caseEditor/InfoBar/InfoBar.vue';

export default {
    name: 'FullScreenModal',
    components: {
        InfoBar,
    },
    props: {
        path: {
            type: Array,
        },
        value: {
            default: true,
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            infoBarHeight: 0,
            scrollBarrier: 72,
            scrolledPast: false,
        };
    },
    mounted() {
        document.body.style.overflow = 'hidden';
        window.addEventListener('scroll', this.onScroll, true);
    },
    methods: {
        closeModal() {
            this.$emit('input', false);
            this.$emit('onClose');
            document.body.style.overflowY = 'scroll';
        },
        onScroll() {
            this.scrolledPast = this.$refs.modal.scrollTop > this.scrollBarrier;
        },
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.onScroll, true);
        document.body.style.overflowY = 'scroll';
    },
};
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.content {
    display: flex;
    background: white;
}

.top-header-action {
    margin: 0 32px 0 32px;
    border-radius: $border-radius-medium $border-radius-medium 0 0;
    height: 48px;
    background-color: #f2f2f7;
    text-align: center;
    border: none;
    width: calc(100% - 64px);
}

.main {
    flex: 1;
    background-color: #f2f2f7;
    padding: 48px;
}

$header-bar-height: 92px;

.full-screen-modal {
    position: fixed;
    width: 100%;
    height: 100%;
    overflow-y: scroll;
    overflow-x: hidden;
    top: 0;
    left: 0;
    background-color: #001e49;
    z-index: 1030;
    animation: fadeIn 200ms ease-out;

    ::v-deep {
        .form:first-child {
            .form-heading {
                margin-top: 0;
            }
        }
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.header-bar {
    background-color: white;
    box-shadow:
        0px 0px 24px rgba(0, 0, 0, 0.1),
        inset 0px -1px 0px $lightest-grey;
    border-radius: $border-radius-medium $border-radius-medium 0 0;
    padding: 0 48px;
    height: $header-bar-height;
    z-index: 1030;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: border-radius 100ms lineair;

    &.stuck {
        border-radius: 0;
    }
}

.header-bar > h2 {
    font-weight: 400;
}
</style>
