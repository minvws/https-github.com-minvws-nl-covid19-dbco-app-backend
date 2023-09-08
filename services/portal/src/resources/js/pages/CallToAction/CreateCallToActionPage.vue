<template>
    <div>
        <div class="return-bar">
            <Link :href="`/editcase/${caseUuid}`" iconLeft="chevron-left">
                {{ $t('pages.createCallToAction.return') }}
            </Link>
        </div>
        <main>
            <header>
                <h1>{{ $t('pages.createCallToAction.title') }}</h1>
            </header>
            <div>
                <h2 class="tw-mb-8">{{ t('pages.createCallToAction.legend') }}</h2>
                <div id="create-cta-form">
                    <CreateCallToAction :caseUuid="caseUuid" @cancel="redirectToCase" @created="redirectToCase" />
                </div>
            </div>
        </main>
    </div>
</template>

<script lang="ts">
import CreateCallToAction from '@/components/callToAction/CreateCallToAction/CreateCallToAction.vue';
import { Link } from '@dbco/ui-library';
import { defineComponent } from 'vue';
import { useI18n } from 'vue-i18n-composable';

export default defineComponent({
    components: {
        CreateCallToAction,
        Link,
    },
    created() {
        void this.$store.dispatch('index/LOAD', this.caseUuid);
    },
    props: {
        caseUuid: { type: String, required: true },
    },
    setup(props) {
        const { t } = useI18n();
        const isCallToActionEnabled = true;

        const redirectToCase = () => {
            window.location.replace(`/editcase/${props.caseUuid}`);
        };

        return {
            isCallToActionEnabled,
            redirectToCase,
            t,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.return-bar {
    background-color: $white;
    display: flex;
    height: 60px;
    padding: 0 $padding-lg;
    width: 100%;

    a {
        align-items: center;
        color: $black;
        display: flex;
        font-weight: 500;

        i {
            background-color: $black;
            transform: rotate(180deg);
            height: 1.375rem;
            width: 1.375rem;
        }
    }
}

main {
    padding: $padding-lg;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: $padding-default;
}

#create-cta-form {
    background-color: $white;
    border-radius: $border-radius-small;
    box-shadow: $box-shadow-card;
    padding: 1.5rem $padding-md;
}
</style>
