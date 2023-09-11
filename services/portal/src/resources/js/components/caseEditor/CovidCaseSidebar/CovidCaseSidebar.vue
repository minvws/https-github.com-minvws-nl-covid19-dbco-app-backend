<template>
    <div :class="['sidebar-wrapper sticky-top mr-0 pr-0', { collapsed }]" ref="sidebar">
        <div class="sidebar d-flex flex-column">
            <button type="button" class="toggle my-5" @click="toggleCollapse">
                <i v-if="!collapsed" class="icon icon--collapse-close" />
                <i v-if="collapsed" class="icon icon--collapse-open" />
            </button>
            <FormRenderer @created="init" :schema="schema" />
        </div>
    </div>
</template>

<script>
import { StoreType } from '@/store/storeType';

export default {
    name: 'CovidCaseSidebar',
    props: {
        schema: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            collapsed: false,
        };
    },
    computed: {
        isModal() {
            return this.$refs.sidebar.closest('.full-screen-modal') !== null;
        },
        textAreaErrors() {
            return this.$store.getters[`${StoreType.INDEX}/errors`].general?.notes;
        },
    },
    mounted() {
        window.addEventListener('resize', this.resize);

        if (this.isModal) {
            // If the context is a modal, set a listener on the modal div
            document.getElementsByClassName('full-screen-modal')[0].addEventListener('scroll', this.setHeight);
        } else {
            // Else set a listener for the window
            window.addEventListener('scroll', this.setHeight);
        }

        this.init();
    },
    destroyed() {
        window.removeEventListener('resize', this.resize);
        window.removeEventListener('scroll', this.setHeight);
    },
    methods: {
        getTextareaElement() {
            return this.$refs.sidebar?.querySelector('textarea[name="general.notes"]');
        },
        init() {
            this.$nextTick(() => {
                this.resize();
            });
        },
        resize() {
            this.setDistanceTop();
            this.setHeight();
        },
        setDistanceTop() {
            if (!this.$refs.sidebar) return;

            let height = 0;
            if (this.isModal) {
                // If the context is a modal set the distance to the top according to the header-bar height
                const stickyElements = document.querySelectorAll(
                    '.full-screen-modal :not(.sidebar-wrapper).sticky-top'
                );
                stickyElements.forEach((el) => (height += el.offsetHeight));
            } else {
                // Else use the navtabs used in the tab context
                const stickyElements = document.querySelectorAll(
                    '*:not(.full-screen-modal) :not(.sidebar-wrapper).sticky-top'
                );
                stickyElements.forEach((el) => (height += el.offsetHeight));
            }

            this.$refs.sidebar.style.top = height + 'px';
        },
        setHeight() {
            const textareaEl = this.getTextareaElement();
            if (textareaEl) {
                // Take potential errors into account
                const errorHeight =
                    textareaEl.closest('.formulate-input').querySelector('.formulate-input-errors')?.offsetHeight || 0;

                // -10px for margin on the bottom
                const textareaHeight = window.innerHeight - textareaEl.getBoundingClientRect().top - errorHeight - 10;
                textareaEl.style.height = `${textareaHeight}px`;
            }

            if (!this.$refs.sidebar) return;
            if (this.isModal) {
                // If the context is a modal, set a listener on the modal div
                const headerBar = document.getElementsByClassName('header-bar')[0];
                this.$refs.sidebar.style.height = `calc(100vh - ${headerBar.getBoundingClientRect().bottom}px)`;
            } else {
                // Dynamically set height on scroll by navtabs position
                const navtabs = document.getElementById('navtabs');
                this.$refs.sidebar.style.height = `calc(100vh - ${navtabs.getBoundingClientRect().bottom}px)`;
            }

            this.setDistanceTop();
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            this.$emit('collapsed', this.collapsed);
        },
    },
    watch: {
        textAreaErrors() {
            this.init();
        },
    },
};
</script>
<style lang="scss" scoped>
@import '@/../scss/variables';

.sidebar-wrapper {
    background-color: white;
    width: $sidebar-width;
    transition: width $sidebar-transition;

    &.collapsed {
        width: 0;
        .sidebar > :not(.toggle) {
            visibility: hidden;
        }
    }

    .sidebar {
        width: 100%;
        height: 100%;
        padding: 8px;
        overflow: visible;
        display: flex;

        .toggle {
            background: none;
            border: none;
            padding: 0px;
            position: absolute;
            display: flex;
            align-items: center;
            top: 0;
            width: 30px;
            height: 40px;
            left: -30px;
            background-color: white;
            border-radius: $border-radius-small 0 0 $border-radius-small;
            cursor: pointer;

            .icon {
                font-size: 20px;
            }
        }

        ::v-deep textarea {
            border: none;
            border-radius: 0;
            border-top: 1px solid $body-bg;
            flex: 1;
            resize: none;
            padding-top: 1rem;
        }
    }
}
</style>
