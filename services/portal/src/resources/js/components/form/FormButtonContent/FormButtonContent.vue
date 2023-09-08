<template>
    <div :class="context.classes.label">{{ context.label }}</div>
</template>

<script>
export default {
    name: 'FormButtonContent',
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
};
</script>
