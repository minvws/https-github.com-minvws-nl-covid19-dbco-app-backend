<template>
    <BModal visible :title="title" v-on="$listeners">
        <p>{{ description }}</p>

        <Collapse :collapsed-size="200" labelOpen="Laat minder zien" :labelClosed="labelCollapseClosed">
            <MessageList
                v-if="validationMessages.fatal.length"
                class="tw-mb-6"
                title="Er zijn een aantal problemen gevonden:"
                validation-level="fatal"
                :messages="validationMessages.fatal"
            />
            <MessageList
                v-if="validationMessages.warning.length"
                class="tw-mb-6"
                title="Verplicht om een melding te doen:"
                validation-level="warning"
                :messages="validationMessages.warning"
            />
            <MessageList
                v-if="validationMessages.notice.length"
                title="Controleer de volgende vragen:"
                validation-level="notice"
                :messages="validationMessages.notice"
            />
        </Collapse>

        <template #modal-footer="{ ok, cancel }">
            <HStack class="tw-w-full">
                <Button variant="outline" class="tw-grow" @click="ok">Toch doorgaan</Button>
                <Button @click="cancel" class="tw-grow">Naar de vragen</Button>
            </HStack>
        </template>
    </BModal>
</template>

<script lang="ts">
import type { CaseValidationMessages } from '@dbco/portal-api/case.dto';
import { Button, Collapse, HStack, Link } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { computed, defineComponent } from 'vue';
import MessageList from './MessageList.vue';

export default defineComponent({
    components: {
        Button,
        HStack,
        MessageList,
        Collapse,
        Link,
    },
    props: {
        validationMessages: {
            type: Object as PropType<CaseValidationMessages>,
            required: true,
        },
    },
    setup({ validationMessages }) {
        const { fatal, warning } = validationMessages;
        const isValid = [fatal, warning].flat().length === 0;

        const labelCollapseClosed = computed(() => {
            const { fatal, warning, notice } = validationMessages;
            const messageCount = [fatal, warning, notice].flat().length;
            return `Laat alle ${messageCount} punten zien`;
        });

        let title = 'Er mist nog informatie om een Osiris melding te doen';
        let description =
            'Wanneer de verplichte vragen niet ingevuld worden, kan er geen Osiris melding worden gedaan.';

        if (isValid) {
            title = 'Controleer of alle Osiris vragen goed zijn ingevuld';
            description = 'Controleer de vragen, en vul deze aan waar nodig.';
        }

        return {
            title,
            description,
            labelCollapseClosed,
        };
    },
});
</script>
