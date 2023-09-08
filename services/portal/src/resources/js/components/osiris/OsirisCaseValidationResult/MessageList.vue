<template>
    <div>
        <HStack as="p" class="tw-body-sm tw-font-bold tw-mb-2" spacing="2">
            <Icon :name="icon" :class="iconStyle" aria-hidden="true" />
            {{ title }}
        </HStack>
        <UnorderedList>
            <ListItem v-for="message in messages" :key="message">{{ message }}</ListItem>
        </UnorderedList>
    </div>
</template>

<script lang="ts">
import type { ValidationLevel } from '@dbco/portal-api/case.dto';
import type { IconName } from '@dbco/ui-library';
import { HStack, Icon, ListItem, UnorderedList } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

const iconStyles: Record<ValidationLevel, string> = {
    fatal: 'tw-text-red-600',
    warning: 'tw-text-red-600',
    notice: 'tw-text-orange-400',
};
const icons: Record<ValidationLevel, IconName> = {
    fatal: 'cross',
    warning: 'exclamation-mark-circle',
    notice: 'exclamation-mark-circle',
};

export default defineComponent({
    name: 'MessageList',
    components: {
        UnorderedList,
        ListItem,
        Icon,
        HStack,
    },
    props: {
        title: {
            type: String,
            required: true,
        },
        validationLevel: {
            type: String as PropType<`${ValidationLevel}`>,
            required: true,
        },
        messages: {
            type: Array as PropType<string[]>,
            required: true,
        },
    },
    setup({ validationLevel }) {
        return {
            icon: icons[validationLevel] as IconName,
            iconStyle: iconStyles[validationLevel],
        };
    },
});
</script>
