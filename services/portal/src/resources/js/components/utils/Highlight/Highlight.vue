<template>
    <span>
        <template v-for="(chunk, $index) in chunks">
            <em v-if="chunk.isHighlighted" :key="$index">{{ chunk.text }}</em>
            <template v-else>{{ chunk.text }}</template>
        </template>
    </span>
</template>

<script lang="ts">
import { computed, defineComponent } from 'vue';
import { escapeRegExp } from 'lodash';

interface Chunk {
    isHighlighted: boolean;
    text: string | undefined;
}

export default defineComponent({
    name: 'Highlight',
    props: {
        text: {
            type: String,
        },
        query: {
            type: String,
            required: true,
        },
    },
    setup(props) {
        const chunks = computed<Chunk[]>(() => {
            if (!(props.query && props.text)) {
                return [
                    {
                        isHighlighted: false,
                        text: props.text,
                    },
                ];
            }

            var iQuery = new RegExp(`(${escapeRegExp(props.query)})`, 'gi');

            return props.text.split(iQuery).map((text) => ({
                isHighlighted: iQuery.test(text),
                text,
            }));
        });

        return {
            chunks,
        };
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

em {
    color: $bco-purple;
    font-style: normal;
}
</style>
