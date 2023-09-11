<template>
    <BModal
        ref="modal"
        :title="$tc('components.modals.CovidCaseDeleteModal.title')"
        :text="text"
        :ok-only="okOnly"
        :ok-title="$tc('components.modals.CovidCaseDeleteModal.okTitle')"
        :ok-variant="okVariant"
        :cancel-title="$tc('components.modals.CovidCaseDeleteModal.cancelTitle')"
        :cancel-variant="cancelVariant"
        @hide="onHide"
        @ok="onConfirm"
    >
        {{ text }}
        <FormInfo class="mt-3 info-block--lg">
            <div>
                <p class="mb-1">
                    <strong>{{ $t(`shared.case_delete_osiris_warning.title`) }}</strong>
                </p>
                <p class="mb-0">
                    {{ $t(`shared.case_delete_osiris_warning.text`, { caseReference }) }}
                </p>
            </div>
        </FormInfo>
    </BModal>
</template>
<script lang="ts">
import type { BModal } from 'bootstrap-vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'CovidCaseDeleteModal',
    components: { FormInfo },
    props: {
        text: {
            type: String,
            required: true,
        },
        okOnly: {
            type: Boolean,
            default: false,
        },
        okVariant: {
            type: String,
            default: 'primary',
        },
        cancelVariant: {
            type: String,
            default: 'outline-primary',
        },
    },
    data() {
        return {
            caseUuid: undefined as string | undefined,
            caseReference: undefined as string | undefined,
        };
    },
    methods: {
        show(caseUuid: string, caseReference: string) {
            // uuid is for future check if osiris information needs to be shown.
            this.caseUuid = caseUuid;
            this.caseReference = caseReference;
            (this.$refs.modal as BModal).show();
        },
        onHide() {
            (this.$refs.modal as BModal).hide();
        },
        onConfirm() {
            this.$emit('confirm', this.caseUuid);
        },
    },
});
</script>
