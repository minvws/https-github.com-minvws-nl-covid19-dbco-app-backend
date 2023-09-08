<template>
    <BModal ref="modal" v-bind="modalConfig" v-on="$listeners" @hide="onHide" @ok="onConfirm">
        <div>{{ typeof text === 'function' ? text() : text }}</div>
    </BModal>
</template>

<script>
import Modal from '../../plugins/modal';

/**
 * Default params used for every call to show(), also used in app.js as BModal defaults
 */
export const defaultParams = {
    cancelTitle: 'Annuleren',
    cancelVariant: 'outline-primary',
    okOnly: false,
    okTitle: 'Ok',
    okVariant: 'primary',
    title: undefined,
    centered: false,
};

export default {
    name: 'BaseModal',
    data() {
        return {
            modalConfig: defaultParams,
            text: undefined,
            onCancel: () => {},
            onConfirm: () => {},
        };
    },
    beforeMount() {
        Modal.EventBus.$on('show', (params) => {
            this.show({ ...params });
        });
        Modal.EventBus.$on('hide', () => {
            this.hide();
        });
    },
    methods: {
        hide() {
            this.$refs.modal.hide();
        },
        onHide({ trigger }) {
            // call onCancel on all possible ways to close the modal (only way to catch ESC), except confirming
            if (trigger === 'ok') return;
            this.onCancel();
        },
        /**
         * Show the BaseModal
         *
         * @param params The params for the modal and callbacks, supports text, onCancel, onConfirm and the BModal properties cancelTitle, cancelVariant, okOnly, okTitle, okVariant and title.
         */
        show(params) {
            // Use the keys from defaultParams to build the config using the provided params, fallback to defaultParams
            this.modalConfig = Object.fromEntries(
                Object.keys(defaultParams).map((key) => [key, params[key] || defaultParams[key]])
            );

            this.text = params.text;
            this.onCancel = params.onCancel || (() => {});
            this.onConfirm = params.onConfirm || (() => {});

            this.$refs.modal.show();
        },
    },
};
</script>

<style lang="scss" scoped>
p {
    margin-bottom: 1.5em;
}
</style>
