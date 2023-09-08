<template>
    <div class="tw-block tw-w-full tw-mb-4">
        <div :class="className">
            Molestiae repellat excepturi laboriosam nihil aut totam tempora. Ratione quos reiciendis porro harum nam
            tenetur minima possimus dolor sed reprehenderit nihil dicta hic. Impedit vel quibusdam deleniti repudiandae
            iure provident incidunt quae sed delectus unde quisquam incidunt. Sit a quas ipsam pariatur error voluptatum
            occaecati ab nam quis. Quis reiciendis tempore ducimus aliquid error nulla accusamus modi quia aspernatur
            iusto ratione quasi facere.
        </div>
        <InfoLabel class="tw-mt-2">
            {{ name }}
        </InfoLabel>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import InfoLabel from './InfoLabel.vue';

export default defineComponent({
    components: {
        InfoLabel,
    },
    props: {
        name: { type: String },
        className: { type: String },
    },
    setup({ className, name }) {
        return {
            name,
            className,
        };
    },
});
</script>
