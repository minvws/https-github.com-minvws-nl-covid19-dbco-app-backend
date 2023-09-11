<template>
    <div class="form-editable-input">
        <BSpinner v-if="data.loading !== undefined && data.loading" class="spinner" variant="primary" />
        <button
            type="button"
            class="form-editable-input-btn"
            aria-label="edit"
            v-else-if="data.disabled !== undefined && !data.editable"
            @click="data.disabled = !data.disabled"
        >
            <img :src="iconEditSvg" aria-hidden="true" alt="" />
        </button>
        <slot />
    </div>
</template>

<script>
import iconEditSvg from '@images/icon-edit.svg';

export default {
    name: 'FormEditableInput',
    props: {
        context: {
            type: Object,
            required: true,
        },
        data: {
            type: Object,
            required: true,
        },
    },
    data() {
        return { iconEditSvg };
    },
};
</script>

<style lang="scss" scoped>
.form-editable-input {
    position: relative;
    width: 100%;

    button.form-editable-input-btn {
        background: none;
        border: none;
        width: auto;
        height: 30px;
    }

    button.form-editable-input-btn,
    .spinner {
        position: absolute;
        top: 35px;
        right: 14px;
        cursor: pointer;
        z-index: 2;
    }
}
</style>
