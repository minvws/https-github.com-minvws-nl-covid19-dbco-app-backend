<template>
    <article id="cta-details">
        <header>
            <h4>{{ $as.defined(callToAction).subject }}</h4>
        </header>
        <div>
            <h5>{{ tc(`components.callToActionSidebar.titles.description`) }}</h5>
            <p>{{ pickedUp ? $as.defined(callToAction).description : '-' }}</p>
        </div>
        <div>
            <h5>{{ tc(`components.callToActionSidebar.titles.deadline`) }}</h5>
            <p>{{ callToAction ? formatDate(parseDate(callToAction.expiresAt), 'dd MMMM yyyy') : '-' }}</p>
        </div>
        <footer>
            <h5>{{ tc(`components.callToActionSidebar.titles.created_by`) }}</h5>
            <p>{{ pickedUp ? createdByInfo : '-' }}</p>
            <small>{{ dateCreated }}</small>
            <small> • {{ tc(`components.callToActionSidebar.titles.created_at`) }}</small>
        </footer>
    </article>
</template>

<script lang="ts">
import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import { formatDate, parseDate } from '@/utils/date';
import type { PropType } from 'vue';
import { computed, defineComponent, unref } from 'vue';
import { useI18n } from 'vue-i18n-composable';

export default defineComponent({
    props: {
        callToAction: { type: Object as PropType<CallToActionResponse | null>, required: true },
        pickedUp: { type: Boolean as PropType<boolean>, required: true },
    },
    setup(props) {
        const { tc } = useI18n();
        const createdByInfo = computed(() => {
            const cta = unref(props.callToAction);
            const name = cta?.createdBy?.name;
            const roles = cta?.createdBy?.roles;
            const translatedRoles = roles ? roles.map((role) => tc(`roles.${role}`)).join(', ') : null;
            return (
                [name, translatedRoles].filter(Boolean).join(', ') ||
                tc('components.callToActionSidebar.hints.no_created_by_info')
            );
        });

        const dateCreated = computed(() => {
            const cta = unref(props.callToAction);
            return cta?.createdAt
                ? formatDate(parseDate(cta.createdAt), 'd MMMM yyyy HH:mm')
                : tc('components.callToActionSidebar.hints.no_creation_date');
        });

        return {
            createdByInfo,
            dateCreated,
            formatDate,
            parseDate,
            tc,
        };
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#cta-details {
    padding: $padding-default;
    padding-bottom: $padding-sm;
    line-height: 1.25rem;

    > *:not(:last-child) {
        margin-bottom: $padding-sm;
    }

    h4 {
        word-break: break-word;
    }

    h5 {
        font-weight: 700;
        font-size: 0.875rem;
        margin: 0;
    }

    p {
        margin-bottom: 0;
        word-break: break-word;
    }

    footer {
        p {
            margin-bottom: $padding-xs;
        }

        small {
            color: $dark-grey;
            font-size: 0.75rem;
        }
    }
}
</style>
