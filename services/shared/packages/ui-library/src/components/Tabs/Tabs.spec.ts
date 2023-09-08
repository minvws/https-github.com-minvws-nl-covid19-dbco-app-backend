import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import type { Ref } from 'vue';
import { defineComponent, ref } from 'vue';
import type { TabsChangeEvent } from '.';
import { TabsContext, TabList, Tab, TabPanels, TabPanel } from '.';
import type { TabVariant } from './use-tabs-state';

const Tabs = defineComponent({
    props: { variant: String },
    components: { TabsContext, TabList, Tab, TabPanels, TabPanel },
    setup(props) {
        const variant = ref(props.variant);
        return { variant };
    },
    template: `<TabsContext :variant="variant">
    <TabList>
        <Tab>Tab 1</Tab>
        <Tab>Tab 2</Tab>
        <Tab>Tab 3</Tab>
    </TabList>

    <TabPanels>
        <TabPanel>Tab 1 content</TabPanel>
        <TabPanel>Tab 2 content</TabPanel>
        <TabPanel>Tab 3 content</TabPanel>
    </TabPanels>
</TabsContext>`,
});

const ControlledTabs = defineComponent({
    props: { initialIndex: Number },
    components: { TabsContext, TabList, Tab, TabPanels, TabPanel },
    setup(props) {
        const index = ref(props.initialIndex);
        function handleTabsChange({ tabIndex }: TabsChangeEvent) {
            index.value = tabIndex;
        }
        return { index, handleTabsChange };
    },
    template: `<TabsContext :index="index" @change="handleTabsChange">
    <TabList>
        <Tab>Tab 1</Tab>
        <Tab>Tab 2</Tab>
        <Tab>Tab 3</Tab>
    </TabList>

    <TabPanels>
        <TabPanel>Tab 1 content</TabPanel>
        <TabPanel>Tab 2 content</TabPanel>
        <TabPanel>Tab 3 content</TabPanel>
    </TabPanels>
</TabsContext>`,
});

type TabsProps = {
    initialIndex?: number;
    isActive?: Ref<boolean>;
    variant?: TabVariant;
};

function createComponent(Component: any, propsData: TabsProps = { variant: 'underline' }) {
    return mount(Component, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

function getSelectedTab(wrapper: ReturnType<typeof createComponent>) {
    return wrapper.find('[role="tab"][aria-selected="true"]');
}
function getSelectedTabPanel(wrapper: ReturnType<typeof createComponent>) {
    return wrapper.find('[role="tabpanel"]:not([hidden])');
}

describe('Tabs', () => {
    it('Tabs should render with the first panel shown', () => {
        const wrapper = createComponent(Tabs);
        expect(getSelectedTab(wrapper).text()).toBe('Tab 1');
        expect(getSelectedTabPanel(wrapper).text()).toBe('Tab 1 content');
    });

    it('Tab active state can be controlled externally', async () => {
        const isActive = ref(false);
        const wrapper = createComponent(Tab, { isActive });
        expect(wrapper.attributes('aria-selected')).toBe('false');
        isActive.value = true;
        await wrapper.vm.$nextTick();
        expect(wrapper.attributes('aria-selected')).toBe('true');
    });

    it('Tabs should switch when a tab is clicked', async () => {
        const wrapper = createComponent(Tabs);
        expect(getSelectedTab(wrapper).text()).toBe('Tab 1');
        expect(getSelectedTabPanel(wrapper).text()).toBe('Tab 1 content');

        await wrapper.find('[role="tab"]:nth-child(2)').trigger('click');
        expect(getSelectedTab(wrapper).text()).toBe('Tab 2');
        expect(getSelectedTabPanel(wrapper).text()).toBe('Tab 2 content');
    });

    it('Controlled Tabs should render with the {index} panel shown', () => {
        const wrapper = createComponent(ControlledTabs, { initialIndex: 2 });
        expect(getSelectedTab(wrapper).text()).toBe('Tab 3');
        expect(getSelectedTabPanel(wrapper).text()).toBe('Tab 3 content');
    });

    it('Controlled Tabs should switch when a tab is clicked', async () => {
        const wrapper = createComponent(ControlledTabs, { initialIndex: 2 });
        expect(getSelectedTab(wrapper).text()).toBe('Tab 3');
        expect(getSelectedTabPanel(wrapper).text()).toBe('Tab 3 content');

        await wrapper.find('[role="tab"]:nth-child(1)').trigger('click');
        expect(getSelectedTab(wrapper).text()).toBe('Tab 1');
        expect(getSelectedTabPanel(wrapper).text()).toBe('Tab 1 content');
    });

    it('Pill Variant Tabs should render with pill styling', () => {
        const wrapper = createComponent(Tabs, { variant: 'pill' });
        expect(wrapper.findComponent(TabList).classes()).include('tw-w-full');
        expect(wrapper.findComponent(Tab).classes()).include('first-of-type:tw-rounded-l');
    });
});
