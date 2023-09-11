import type { VueConstructor } from 'vue';

export const withLayout = (component: VueConstructor) => ({
    name: 'navbar',
    props: { wraps: { type: Function, default: component } },
    template: `
        <div>
            <div class="dbco-header">
            </div>
            <component :is="wraps" v-bind="$attrs" v-on="$listeners" />
        </div>
    `,
    components: {},
});
