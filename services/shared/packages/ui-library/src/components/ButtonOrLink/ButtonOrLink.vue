<template>
    <component :is="as" :aria-label="ariaLabel" :disabled="disabled" v-on="$listeners" v-bind="bindings">
        <slot />
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { computed, toRefs, defineComponent } from 'vue';
import type { HTMLAnchorElementTarget } from '../../types';
import { unrefAll } from '../../utils';

export type ButtonOrLinkElement = 'a' | 'button' | 'router-link' | 'span';

export default defineComponent({
    props: {
        href: { type: String },
        rel: { type: String },
        target: { type: String as PropType<HTMLAnchorElementTarget> },
        ariaLabel: { type: String },
        to: { type: String },
        disabled: { type: Boolean },
        type: { type: String as PropType<'button' | 'submit' | 'reset'>, default: 'button' },
    },
    setup(props) {
        const { ariaLabel, target, href, rel: relProp, to, disabled, type } = toRefs(props);

        if (href.value !== undefined && to.value !== undefined) {
            console.warn(
                'ButtonOrLink :: Can not use both `href` and `to` props at the same time, `href` will be ignored!'
            );
        }

        if (target.value === '_blank' && !ariaLabel.value) {
            console.warn('ButtonOrLink :: Outgoing links (target="_blank") must have an `aria-label` set!');
        }

        const as = computed<ButtonOrLinkElement>(() => {
            let element: ButtonOrLinkElement = 'button';

            if (to.value !== undefined) {
                element = 'router-link';
            } else if (href.value !== undefined) {
                element = 'a';
            }

            if ((element === 'a' || element === 'router-link') && disabled.value) {
                return 'span'; // span is used to render a disabled link
            }

            return element;
        });

        const rel = computed(() => {
            if (target.value === '_blank') {
                return relProp.value || 'noopener';
            }
            return relProp.value;
        });

        const bindings = computed(() => {
            switch (as.value) {
                case 'a':
                    return unrefAll({
                        href,
                        target,
                        rel,
                    });
                case 'router-link':
                    return unrefAll({
                        to,
                        target,
                        rel,
                    });
                case 'button':
                    return unrefAll({
                        disabled,
                        type,
                    });
                case 'span':
                    return {};
                default:
                    /* c8 ignore next */
                    throw new Error(`ButtonOrLink :: unknown element type ${as.value}`);
            }
        });

        return {
            as,
            ariaLabel,
            bindings,
        };
    },
});
</script>
