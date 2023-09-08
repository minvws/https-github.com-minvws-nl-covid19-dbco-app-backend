<template>
    <div v-if="loaded" class="container form-container px-0">
        <FormulateFormWrapper
            v-on="$listeners"
            v-for="(chapter, $index) in schema"
            :key="$index"
            class="form"
            v-model="fragments"
            :errors="errors"
            :schema="[chapter]"
            @change="submit"
            @repeatableRemoved="repeatableRemoved"
        />
        <div v-if="showDebug" class="mt-4 pt-4 pb-4 debug container">
            <h4>Debug</h4>
            ID: {{ _uid }}<br />
            Store: {{ storeType }}<br />
            Fragments
            <pre> {{ fragments }}</pre>
            Rules
            <ul>
                <li v-for="(rule, $index) in rules" :key="$index">
                    {{ rule.title }}<br />
                    Fields: {{ rule.watch }}
                </li>
            </ul>
            Errors
            <pre> {{ errors }} </pre>
            <pre>{{ schema }}</pre>
        </div>
    </div>
    <div v-else class="mb-5 mt-4 text-center">
        <BSpinner class="spinner" variant="primary" />
    </div>
</template>

<script lang="ts">
import { SharedActions } from '@/store/actions';
import { StoreType } from '@/store/storeType';
import { TaskActions } from '@/store/task/taskActions';
import { flatten, setPath, unflatten } from '@/utils/object';
import { processRules } from '@/utils/schema';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { FormField } from '../ts/formTypes';
import type { SchemaRule } from '../ts/schemaType';

type FormEvent = Event & { name: string; values: object };

export default defineComponent({
    name: 'FormRenderer',
    data() {
        return {
            loaded: false,
            localChanges: undefined as undefined | Record<any, any>,
            localErrors: {},
        };
    },
    props: {
        rules: {
            type: Array as PropType<SchemaRule<AnyObject>[]>,
            required: false,
            default: () => [],
        },
        schema: {
            type: Array as PropType<FormField[]>,
            required: true,
        },
        showDebug: {
            type: Boolean,
            required: false,
            default: false,
        },
        storeType: {
            type: String as PropType<StoreType>,
            required: false,
            default: StoreType.INDEX,
        },
    },
    computed: {
        errors(): any {
            return flatten(this.$store.getters[`${this.storeType}/errors`]);
        },
        fragments: {
            get(): any {
                return flatten(Object.assign({}, this.$store.getters[`${this.storeType}/fragments`]));
            },
            set(data: AnyObject) {
                this.localChanges = data;
            },
        },
    },
    created() {
        // Initialize localChanges to properly handle changes and rules
        this.localChanges = this.fragments;

        // Ensures lazy loaded tab switches
        setTimeout(() => (this.loaded = true), 0);
    },
    methods: {
        repeatableRemoved(e: FormEvent) {
            const { name, values } = e;

            this.localChanges = {};
            this.localChanges[name] = values;

            void this.submit(e);
        },
        submit(event: FormEvent) {
            // Prevent change event from bubbling up to increase rendering performance
            if (event && event.stopPropagation) event.stopPropagation();

            // Do not submit or process rules if localChanges have not been set
            if (!this.localChanges) return;

            const changes = processRules(this.rules, this.fragments, this.localChanges);
            const data = unflatten(changes);

            for (const [path, value] of Object.entries(changes)) {
                setPath(path, data, value);
            }

            void this.dispatchStoreUpdate(data);
        },
        dispatchStoreUpdate(data: any) {
            if (this.storeType === StoreType.TASK)
                return this.$store.dispatch(`${StoreType.TASK}/${TaskActions.UPDATE_TASK_FRAGMENT}`, data);
            return this.$store.dispatch(`${this.storeType}/${SharedActions.UPDATE_FORM_VALUE}`, data);
        },
    },
    provide() {
        return {
            submitForm: this.submit,
        };
    },
});
</script>

<style scoped lang="scss">
.spinner {
    width: 2rem;
    height: 2rem;
}
</style>
