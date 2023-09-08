<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    functional: true,
    props: {
        shouldWrap: { type: Boolean, required: true },
    },
    render: (h, ctx) => {
        const { data, children, props } = ctx;
        if (children.length !== 1) {
            throw new Error('this component accepts only one root node in its slot');
        }

        if (props.shouldWrap) {
            return h('div', data, children);
        }

        return children;
    },
});
</script>
