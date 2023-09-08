import type { Meta } from '@storybook/vue';
import { TabsContext, Tab, TabList, TabPanel, TabPanels } from '.';
import { Button } from '..';
import { ref } from 'vue';
import { setupStory } from '../../../docs/utils/story';

const story: Meta = {
    title: 'Components/Tabs',
    component: TabsContext,
    parameters: {
        docs: {
            description: {
                component: `A component that helps you build tabs.

### TODO's

__Design:__

[ ] Support long lists of tabs with a "more" dropdown.

__Accessibility:__

[ ] Keyboard support, as [described here](https://www.w3.org/WAI/ARIA/apg/example-index/tabs/tabs-manual.html).

<br>

### \`RouterTab\`
It is also possible to use a \`TabList\` separately in combination with the [Vue Router](https://v3.router.vuejs.org/) by using the \`RouterTab\`. The \`RouterTab\` will use the \`isExactActive\` state from the \`router-link\` itself instead of the \`TabsContext\`. (So the tab will be active when the \`to\` path matches the current route)

To use the \`RouterTab\` you will need to go through a couple of steps:

1. Add a route to the router (currently in the \`router.ts\` file)  

    \`\`\`ts
    {
        path: '/someroute/:view',
        component: SomeComponent,
        props: route => ({ view: route.params.view })
    },
    \`\`\`

2. Add the \`TabList\` to your page with \`RouterTab\` components.

    \`\`\`html
    <TabList>
        <RouterTab to="/someroute/first">First Tab</RouterTab>
        <RouterTab to="/someroute/second">Second Tab</RouterTab>
    </TabList>
    \`\`\`

3. Add a \`router-view\` to your page.

    \`\`\`html
    <router-view
        @someEmit="doSomething()"
        :someProp="someData"
    />
    \`\`\`

4. Last but not least, add the route also to the server configuration. (Currently in the \`web.php\` file)

    \`\`\`php
    
    Route::get('/someroute/{view?}', [SomeController::class, 'someControllerMethod'])
    \`\`\`

> Note that there isn't a story for the \`RouterTab\`. The Vue Router is not part of the \`ui-library\` and the \`RouterTab\` relies on a globally registered \`router-link\` component.

<br>

### \`TabsContext\`

The \`TabsContext\` component is a wrapper component that will manage the state of the tabs. It will keep track of the current tab index and will emit an \`change\` event when the tab index changes. The \`change\` event will contain the new tab index.

The \`Tab\` and \`TabPanel\` are linked by their index. The first \`Tab\` will be linked to the first \`TabPanel\` and so on.

The \`TabsContext\` will manage its own state automatically. If you want to manually set the current tab index you can use the \`index\` prop. See the _Controlled_ story for an example.
`,
            },
        },
    },
};

export const Default = setupStory({
    components: { TabsContext, Tab, TabList, TabPanel, TabPanels },
    template: `
    <TabsContext>
        <TabList>
            <Tab>Tab 1</Tab>
            <Tab>Tab 2</Tab>
            <Tab>Tab 3</Tab>
        </TabList>

        <TabPanels class="tw-p-4">
            <TabPanel>Tab 1 content</TabPanel>
            <TabPanel>Tab 2 content</TabPanel>
            <TabPanel>Tab 3 content</TabPanel>
        </TabPanels>
    </TabsContext>
    `,
});

export const Controlled = setupStory({
    description: 'You can manually set the current tab index via the `TabsContext`.',
    components: { TabsContext, Tab, TabList, TabPanel, TabPanels, Button },
    setup() {
        const currentIndex = ref(1);
        const switchTab = (to: number) => {
            currentIndex.value = to;
        };

        return { currentIndex, switchTab };
    },
    template: `
    <TabsContext :index="currentIndex" @change="event => switchTab(event.tabIndex)">
        <TabList>
            <Tab>Tab 1</Tab>
            <Tab>Tab 2</Tab>
            <Tab>Tab 3</Tab>
        </TabList>
        
        <TabPanels class="tw-p-4">
            <TabPanel>
                <p>Tab 1 content</p>
                <Button size="sm" @click="switchTab(1)">Next</Button>
            </TabPanel>
            <TabPanel >
                <p>Tab 2 content</p>
                <Button size="sm" @click="switchTab(2)">Next</Button>
            </TabPanel>
            <TabPanel>
                <p>Tab 3 content</p>
                <Button size="sm" @click="switchTab(0)">Back to start</Button>
            </TabPanel>
        </TabPanels>
    </TabsContext>
    `,
});

export const PillVariant = setupStory({
    components: { TabsContext, Tab, TabList, TabPanel, TabPanels },
    template: `
    <TabsContext variant="pill">
        <TabList>
            <Tab>Tab 1</Tab>
            <Tab>Tab 2</Tab>
            <Tab>Tab 3</Tab>
        </TabList>

        <TabPanels class="tw-p-4">
            <TabPanel>Tab 1 content</TabPanel>
            <TabPanel>Tab 2 content</TabPanel>
            <TabPanel>Tab 3 content</TabPanel>
        </TabPanels>
    </TabsContext>
    `,
});

export default story;
