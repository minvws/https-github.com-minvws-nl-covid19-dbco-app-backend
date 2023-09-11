<template>
    <div v-if="context.model" @input="onInput">
        <FormulateFormWrapper
            v-model="context.model"
            @validation="onValidation"
            @keydown="onKeyDown"
            :name="prefix"
            :errors="errors"
            :schema="[schema]"
        />
    </div>
</template>

<script>
import { addressApi } from '@dbco/portal-api';
import { formatPostalCode } from '@/utils/formatPostalCode';
import _ from 'lodash';
import axios from 'axios';

export default {
    name: 'FormAddressLookup',
    inject: {
        observeErrors: {
            default: false,
        },
    },
    data() {
        return {
            cancelToken: null,
            errors: {},
            hasErrors: false,
            lastAddress: this.context.model ? `${this.context.model.postalCode}-${this.context.model.houseNumber}` : '',
            prefilled: false,
        };
    },
    props: {
        context: {
            type: Object,
            required: true,
        },
        schema: {
            type: Object,
            required: true,
        },
        prefix: {
            type: String,
            required: false,
            default: '',
        },
    },
    created() {
        this.context.model = this.context.model || {
            postalCode: '',
            street: '',
            houseNumber: '',
            houseNumberSuffix: '',
            town: '',
        };

        this.schemaFields
            // Gets all field names within schema
            .filter((field) => field.data)
            .map((field) => field.data.name)
            .forEach((field) => {
                this.observeErrors({
                    callback: (errors) => {
                        this.errors = { ...this.errors, [field]: errors };
                    },
                    type: 'input',
                    field,
                });
            });
    },
    computed: {
        schemaFields() {
            const groupChildren = this.schema.children.map((group) => group.children);

            // Gets an array of all the fields in the groups
            return groupChildren.reduce((children, group) => [...children, ...group[0].children], []);
        },
    },
    methods: {
        debouncedAddressCheck: _.debounce(function (postalCode, houseNumber) {
            this.addressCheck(postalCode, houseNumber);
        }, 300),

        onKeyDown(e) {
            if (e.target.name !== 'houseNumber') return;

            // Prevent entering minus sign
            if (e.keyCode === 109 || e.keyCode === 189 || e.key === '-') e.preventDefault();
            // Prevent entering plus sign
            if (e.keyCode === 107 || e.keyCode === 187 || e.key === '+') e.preventDefault();
        },

        async addressCheck(postalCode, houseNumber) {
            // Don't execute call if the form has errors (e.g. postalCode invalid)
            if (this.hasErrors) {
                this.$store.commit('organisation/SET_CURRENT_FROM_ADDRESS_SEARCH', undefined);
                return;
            }

            this.errors = {};

            // Set new cancellation token
            this.cancelToken = axios.CancelToken.source();
            this.setLoading(true);

            try {
                const { address, organisationUuid } = await addressApi.search(
                    postalCode,
                    houseNumber,
                    this.cancelToken.token
                );

                this.$store.commit('organisation/SET_CURRENT_FROM_ADDRESS_SEARCH', organisationUuid);
                this.context.model = { ...this.context.model, street: address.street, town: address.town };
                this.prefilled = true;
            } catch (ex) {
                // If request is cancelled, don't assign errors
                if (axios.isCancel(ex)) return;

                this.context.model = { ...this.context.model, street: '', town: '' };
                this.prefilled = false;

                // Set the error at the end of the call stack, since it will reset immediately by the store otherwise
                const error = ex.response.data.error;
                setTimeout(() => (this.errors = { street: [JSON.stringify({ warning: [error] })] }));
            } finally {
                this.setLoading(false);
            }

            // Make sure to save the results
            this.$emit('change');
        },
        onInput($e) {
            let data = { ...this.context.model };
            data[$e.target.name] = $e.target.value;

            const { postalCode, houseNumber } = data;
            const formattedPostalCode = formatPostalCode(postalCode);
            if (!formattedPostalCode || !houseNumber) {
                this.lastAddress = '';
                return;
            }

            const current = `${formattedPostalCode}-${houseNumber}`;
            if (current === this.lastAddress) return;
            this.lastAddress = current;

            // Cancel previous call to prevent race condition
            if (this.cancelToken) {
                this.cancelToken.cancel();
            }

            // Save the current state before we start a debounced mutation using the address check
            this.$emit('change');
            this.debouncedAddressCheck(formattedPostalCode, houseNumber);
        },
        onValidation($event) {
            this.hasErrors = $event.hasErrors;
            this.errors = { [$event.name]: [JSON.stringify({ warning: $event.errors })] };
        },
        setLoading(isLoading) {
            this.schemaFields.forEach((field) => {
                if (field.data && 'loading' in field.data) {
                    field.data.loading = isLoading;
                }
            });
        },
        setEditable(isEditable) {
            this.schemaFields.forEach((field) => {
                if (field.data && 'editable' in field.data) {
                    field.data.disabled = isEditable;
                    field.data.editable = !isEditable;
                }
            });
        },
    },
    watch: {
        prefilled: {
            handler(value) {
                this.setEditable(value);
            },
            immediate: true,
        },
    },
};
</script>
