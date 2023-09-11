import type { StoryFn } from '@storybook/vue';
import { getCurrentInstance } from 'vue';
import type { ComponentOptions } from 'vue';

type ComponentOptionsVue = ComponentOptions<Vue>;

export interface StoryConfig<P extends GenericObject = Record<string, unknown>> {
    components?: ComponentOptionsVue['components'];
    template: string;
    description?: string;
    props?: P;
    setup?: ComponentOptionsVue['setup'];
}

/**
 * A helper to setup a story with less boilerplate code.
 * Will automatically supply the args from the controls and provided props to the story template.
 *
 * @example
 * export const Example = setupStory({
 *     components: { Component },
 *     template: `
 *         <Component v-bind="args" @change="onChange">
 *             {{ label }}
 *         </Component/>
 *     `,
 *     props: { onChange: action('change'), label: 'Label' },
 * });
 *
 * @example
 * export const ExampleState = setupStory({
 *     components: { Component, Button },
 *     setup(props, context) {
 *         const currentIndex = ref(props.initialIndex);
 *         const setIndex = (to: number) => {
 *             currentIndex.value = to;
 *         };
 *         return { currentIndex, setIndex };
 *     },
 *     template: `
 *     <Component v-bind="args">
 *         currentIndex: {{ currentIndex }}
 *         <Button @click="setIndex(currentIndex + 1)">Increase</Button>
 *     </Component/>
 *     `,
 *      props: { initialIndex: 0 },
 * });
 */
export function setupStory<P extends GenericObject>({
    template,
    components,
    setup: storySetup,
    props: storyProps,
    description,
}: StoryConfig<P>): StoryFn {
    let component: ComponentOptions<Vue> | null = null;
    let currentInstance: ReturnType<typeof getCurrentInstance> = null;

    const Story: StoryFn = ({ ...args }) => {
        /**
         * We manually update the args on the component instance and force an update
         * to make sure the controls are working. This is a workaround for the a bug in Storybook.
         * @see: [Bug]: After upgrading to V7 controls are not interactable - https://github.com/storybookjs/storybook/issues/22227
         */
        if (component) {
            if (!currentInstance) {
                console.warn(
                    'component is set but the currentInstance is null, this should not happen and will likely break the reactivity of the storybook controls.'
                );
            } else {
                (currentInstance.proxy as GenericObject).args = args;
                currentInstance.proxy.$forceUpdate();
                return component;
            }
        }

        component = {
            components,
            setup(props, context) {
                currentInstance = getCurrentInstance();
                const storySetupProps = typeof storySetup === 'function' ? storySetup(props, context) : {};
                const allProps = Object.assign({}, storyProps, storySetupProps);

                if (allProps['args'] || allProps['props']) {
                    throw new Error('The StoryConfig props object cannot contain the keys "args" or "props".');
                }

                return { args, props: allProps, ...allProps };
            },
            template,
        };

        return component;
    };

    Story.parameters = {
        docs: {
            description: {
                story: description,
            },
        },
    };

    return Story;
}
