<template>
    <div>
        <ul v-for="error in visibleErrors" :key="error.type" :class="[outerClass, 'mt-1']">
            <li
                v-for="message in error.messages"
                :key="message"
                :class="[itemClass, `error-type-${error.type}`, 'd-flex align-items-center mb-2']"
                role="status"
                aria-live="polite"
            >
                <i :class="['icon m-1 mr-2', icons[error.type]]"></i>{{ message }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'FormErrors',
    inject: {
        observeErrors: {
            default: false,
        },
        removeErrorObserver: {
            default: false,
        },
        observeContext: {
            default: false,
        },
        removeContextObserver: {
            default: false,
        },
    },
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            boundSetErrors: this.setErrors.bind(this),
            boundSetFormContext: this.setFormContext.bind(this),
            localErrors: {},
            formContext: {
                classes: {
                    formErrors: 'formulate-form-errors',
                    formError: 'formulate-form-error',
                },
            },
            icons: {
                fatal: 'icon--error',
                warning: 'icon--error-warning',
                notice: 'icon--error-notice',
            },
        };
    },
    computed: {
        isFocused() {
            if (!this.context.attributes.showValidation || this.context.attributes.showValidation != 'blur')
                return false;

            if (this.$parent.$el) {
                // Return if this form element (VueFormulate div) contains the documents active element (input field)
                return this.$parent.$el.contains(document.activeElement);
            }

            return false;
        },
        visibleValidationErrors() {
            return Array.isArray(this.context.visibleValidationErrors) && !this.isFocused
                ? this.context.visibleValidationErrors
                : [];
        },
        errors() {
            if (Array.isArray(this.context.errors) && this.context.errors.length > 0) {
                return JSON.parse(this.context.errors[0]);
            }

            return {};
        },
        mergedErrors() {
            if (this.visibleValidationErrors.length > 0) {
                this.errors.fatal = this.errors.fatal || [];
                this.errors.warning = this.errors.warning || [];
                this.errors.notice = this.errors.notice || [];

                // If no backend errors
                if (Object.values(this.errors).every((arr) => arr.length === 0)) {
                    // Add frontend errors as warnings
                    this.errors['warning'] = Array.from(new Set(this.visibleValidationErrors)).filter(
                        (message) => typeof message === 'string'
                    );
                }
            }
            return this.errors;
        },
        visibleErrors() {
            return Object.keys(this.mergedErrors)
                .map((type) => {
                    return {
                        messages: this.mergedErrors[type],
                        type,
                    };
                })
                .sort((a, b) => a.type - b.type);
        },
        outerClass() {
            return this.context.classes.errors;
        },
        itemClass() {
            return this.context.classes.error;
        },
    },
    created() {
        if (this.observeErrors) {
            this.observeErrors({ callback: this.boundSetErrors, type: 'input', field: this.context.name });
        }
    },
    methods: {
        setErrors(errors) {
            if (errors.length > 0) {
                errors = JSON.parse(errors[0]);
            }

            this.localErrors = errors;
        },
        setFormContext(context) {
            this.formContext = context;
        },
    },
};
</script>
