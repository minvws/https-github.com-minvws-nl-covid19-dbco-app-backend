<template>
    <Heading size="sm" as="h2" :class="headingStyles"><slot /></Heading>
</template>

<script lang="ts" setup>
import { Heading } from '@dbco/ui-library';

const headingStyles = [
    'tw-mb-8',
    'tw-relative',
    'tw-w-100',
    'tw-max-w-none',
    `after:tw-content-['']`,
    'after:tw-absolute',
    'after:tw-top-[50%]',
    'after:tw-left-[70px]',
    'after:tw-w-[calc(100%-70px)]',
    'after:tw-h-[1px]',
    'after:tw-bg-gray-200',
];
</script>
