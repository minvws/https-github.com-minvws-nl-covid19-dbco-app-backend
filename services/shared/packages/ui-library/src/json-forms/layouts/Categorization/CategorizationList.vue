<template>
    <nav>
        <ul>
            <li
                v-for="(category, $index) in categorization.elements"
                :key="`tab-${$index}`"
                :class="{ active: selectedCategory === category }"
            >
                <a role="tab" tabindex="-1" @click="$emit('select', category)" @keydown="$emit('select', category)">
                    {{ category.label }}
                </a>
            </li>
        </ul>
    </nav>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Categorization, Category } from '@jsonforms/core';

export default defineComponent({
    name: 'CategorizationList',
    props: {
        categorization: {
            type: Object as PropType<Categorization>,
            required: true,
        },
        selectedCategory: {
            type: Object as PropType<Category>,
        },
    },
    setup() {
        return {};
    },
});
</script>

<style lang="scss" scoped>
nav {
    background: white;
    padding: 0 1rem;
    width: 100%;
    position: relative;

    &:before,
    &:after {
        content: '';
        position: absolute;
        width: 1000px;
        height: 100%;
        top: 0;
        left: -1000px;
        background-color: #fff;
    }

    &:after {
        left: auto;
        right: -1000px;
    }

    ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: row;
        gap: 1.5rem;

        li {
            border-bottom: 4px solid transparent;
            margin: 0;

            a {
                cursor: pointer;
                color: #576578;
                display: block;
                padding: 1rem 0;
            }

            &.active {
                border-bottom-color: #5616ff;
                color: #001e49;
            }
        }
    }
}
</style>
