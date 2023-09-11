<template>
    <div data-testid="messages-wrapper" class="messages-wrapper">
        <div v-if="messages.length === 0" data-testid="no-messages">Er zijn nog geen verzonden berichten.</div>
        <BTableSimple
            class="table-ggd w-100 table--spacious table--padding-cells-left-none table--clickable"
            v-if="messages.length > 0"
        >
            <BThead>
                <BTr>
                    <BTh>Onderwerp</BTh>
                    <BTh>Verstuurd naar</BTh>
                    <BTh>Beschikbaar T/M</BTh>
                    <BTh>Verzonden op</BTh>
                </BTr>
            </BThead>
            <BTbody>
                <BTr v-for="message in filteredMessages" @click="$emit('select', message.uuid)" :key="message.uuid">
                    <BTd>
                        <strong>{{ message.subject }}</strong>
                        <AttachmentIcon
                            v-if="message.hasAttachments"
                            width="14px"
                            class="ml-2"
                            data-testid="icon-attachment"
                        />
                    </BTd>
                    <BTd>{{ message.toEmail }}</BTd>
                    <BTd v-if="message.isExpired">Niet meer beschikbaar</BTd>
                    <BTd v-else-if="message.isDeleted">Bericht ingetrokken</BTd>
                    <BTd v-else>{{ $filters.dateTimeFormatLong(message.expiresAt) }}</BTd>
                    <BTd>{{ $filters.dateTimeFormatLong(message.createdAt) }}</BTd>
                </BTr>
            </BTbody>
        </BTableSimple>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { StoreType } from '@/store/storeType';
import { mapGetters } from '@/utils/vuex';
import AttachmentIcon from '@icons/attachment.svg?vue';
import type { MessageSummary } from '@dbco/portal-api/message.dto';

export default defineComponent({
    name: 'Messages',
    components: {
        AttachmentIcon,
    },
    props: {
        taskUuid: {
            type: String,
            required: false,
            default: null,
        },
    },
    async created() {
        await this.getMessages();
    },
    computed: {
        ...mapGetters(StoreType.INDEX, ['messages']),
        caseUuid() {
            return this.$store.getters['index/uuid'];
        },
        filteredMessages() {
            return this.messages.filter((message: MessageSummary) => {
                return message.caseUuid === this.caseUuid && message.taskUuid === this.taskUuid;
            });
        },
    },
    methods: {
        async getMessages() {
            await this.$store.dispatch('index/LOAD_MESSAGES');
        },
    },
});
</script>

<style lang="scss">
.messages-wrapper {
    .table {
        tr {
            td {
                strong {
                    font-weight: 500;
                }
            }
        }
    }
}
</style>
