<template>
    <ul class="tw-p-0 tw-m-0" v-tw-merge>
        <slot />
    </ul>
</template>

<style lang="css" scoped>
li {
    display: flex;
    align-items: baseline;
}

li:not(:last-child) {
    @apply tw-mb-2;
}

li::before {
    content: '';
    position: relative;
    width: 0.3em;
    height: 0.3em;
    top: -0.2em;
    flex: 0 0 auto;
    border-radius: 50%;
    margin-right: 0.5em;
    background: currentcolor;
}
</style>
