import type { Meta, StoryFn } from '@storybook/vue';
import { TestBox } from '../../../docs/components';
import LayoutSidebar from './LayoutSidebar.vue';

type SidebarPosition = 'left' | 'right';

const story: Meta = {
    title: 'Components/LayoutSidebar',
    component: LayoutSidebar,
    parameters: {
        docs: {
            description: {
                component:
                    'This component provides a layout with a main area and a sidebar, each with their own scroll container. It also has the option to take up the remaining space inside a flex container.',
            },
        },
    },
    argTypes: {
        sidebarPosition: {
            options: ['left', 'right'] as SidebarPosition[],
            control: {
                type: 'select',
            },
        },
    },
};

interface StoryProps {
    sidebarPosition: SidebarPosition;
}

const DefaultTemplate: StoryFn<StoryProps> = ({ sidebarPosition }) => {
    return {
        components: { LayoutSidebar, TestBox },
        setup() {
            return { sidebarPosition };
        },
        template: `
        <TestBox noPadding color="gray" class="tw-h-[400px]">
            <LayoutSidebar :sidebarPosition="sidebarPosition">
                <TestBox color='green' centerContent class="tw-flex tw-flex-col tw-flex-auto">
                    Main content
                </TestBox>

                <template #sidebar>
                    <TestBox color='blue' centerContent class="tw-flex tw-flex-col tw-flex-auto">
                        Sidebar
                    </TestBox>
                </template>
            </LayoutSidebar>
        </TestBox>
        `,
    };
};

const ScrollTemplate: StoryFn<StoryProps> = () => {
    return {
        components: { LayoutSidebar, TestBox },
        template: `
        <TestBox noPadding  color="gray" class="tw-h-[600px]">
            <LayoutSidebar>
                <TestBox color='green' centerContent v-for="n in 100" :key="n">
                    Main content: {{n}}
                </TestBox>

                <template #sidebar>
                    <TestBox color='blue' centerContent v-for="n in 50" :key="n">
                        Sidebar: {{n}}
                    </TestBox>
                </template>
            </LayoutSidebar>
        </TestBox>
        `,
    };
};

const FlexSpaceTemplate: StoryFn<StoryProps> = () => {
    return {
        components: { LayoutSidebar, TestBox },
        template: `
        <div class="tw-pb-16">
            <TestBox color="gray" variant="outline" class="tw-p-0 tw-h-[600px] tw-inline-flex tw-flex-col tw-mb-10 tw-w-[400px]">
                <TestBox color="yellow">
                    <h3>with <i>useFlexSpace</i></h3>
                    <p>
                        The component will render with an extra wrapper which ensures the component will take on the remaining space inside the a flex layout component. 
                    </p>
                </TestBox>

                <LayoutSidebar useFlexSpace>
                    <TestBox color='green' centerContent v-for="n in 100" :key="n">
                        Main content: {{n}}
                    </TestBox>

                    <template #sidebar>
                        <TestBox color='blue' centerContent v-for="n in 50" :key="n">
                            Sidebar: {{n}}
                        </TestBox>
                    </template>
                </LayoutSidebar>
            </TestBox>

            <TestBox color="gray" variant="outline" class="tw-p-0 tw-h-[600px] tw-inline-flex tw-flex-col tw-mb-10 tw-w-[400px]">
                <TestBox color="yellow">
                    <h3>without <i>useFlexSpace</i></h3>
                    <p>Without this property it might break outside of the parent container. Though still limited to max-height: 100% by default.</p>
                </TestBox>

                <LayoutSidebar>
                    <TestBox color='green' centerContent v-for="n in 100" :key="n">
                        Main content: {{n}}
                    </TestBox>

                    <template #sidebar>
                        <TestBox color='blue' centerContent v-for="n in 50" :key="n">
                            Sidebar: {{n}}
                        </TestBox>
                    </template>
                </LayoutSidebar>
            </TestBox>
        </div>
        `,
    };
};

export const Default = DefaultTemplate.bind({});
export const Scroll = ScrollTemplate.bind({});
export const FlexSpace = FlexSpaceTemplate.bind({});

export default story;
