<template>
    <div class="button-toggle-group my-5 py-5">
        <div class="button-toggle-group__content">
            <h3 class="form-heading mt-0">{{ titleValue }}</h3>
            <div class="form-chapter p-4 mb-0">
                <BRow v-if="labelComponent">
                    <BCol cols="auto">
                        <component :is="labelComponent" />
                    </BCol>
                </BRow>
                <BRow>
                    <BCol v-show="!isOpen" cols="auto" class="pr-0">
                        <BButton
                            :disabled="disabled"
                            variant="primary"
                            @click="buttonClicked = true"
                            data-testid="button-show-group"
                            >{{ buttonTextValue }}</BButton
                        >
                    </BCol>
                    <BCol cols="auto" :class="{ 'pl-2': !isOpen }" v-if="buttonComponent">
                        <component :is="buttonComponent" :disabled="disabled" />
                    </BCol>
                </BRow>
            </div>
            <div v-show="isOpen" @change="onChange">
                <slot />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { formLabelValue } from '@/utils/form';
import { getInputNames } from '@/utils/schema';
import type { Children } from '@/formSchemas/schemaGenerator';
import type { FormLabel, VueFormulateContext } from '../ts/formTypes';

export default defineComponent({
    name: 'FormButtonToggleGroup',
    props: {
        buttonText: {
            type: [String, Function] as PropType<FormLabel>,
            required: true,
        },
        context: {
            type: Object as PropType<VueFormulateContext>,
            required: true,
        },
        childrenSchema: {
            type: Array as PropType<Children<any>>,
            required: true,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
        labelComponent: {
            type: [Function, Object] as PropType<any>,
            required: false,
        },
        buttonComponent: {
            type: [Function, Object] as PropType<any>,
            required: false,
        },
        title: {
            type: [String, Function] as PropType<FormLabel>,
            required: true,
        },
    },
    inject: ['rootModel'],
    data() {
        return {
            buttonClicked: false,
        };
    },
    methods: {
        onChange() {
            this.$emit('change');
        },
    },
    computed: {
        isOpen() {
            return this.buttonClicked || this.hasValues;
        },
        hasValues() {
            const rootModel = (this as any).rootModel();

            return getInputNames(this.childrenSchema).some(
                (name) =>
                    rootModel.hasOwnProperty(name) &&
                    (!Array.isArray(rootModel[name]) ? rootModel[name] !== null : rootModel[name].length > 0)
            );
        },
        titleValue() {
            return formLabelValue(this.title);
        },
        buttonTextValue() {
            return formLabelValue(this.buttonText);
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.button-toggle-group {
    position: relative;

    &::before {
        background-color: $lightest-grey;
        box-shadow: inset 0px 2px 10px rgba(0, 0, 0, 0.05);
        content: '';
        display: block;
        overflow: hidden;
        position: absolute;
        left: -100vw;
        right: -100vw;
        top: 0;
        bottom: 0;
    }

    &__content {
        position: relative;
    }
}
</style>
