import { mount } from '@vue/test-utils';
// @ts-ignore
import type { Props } from './DbcoMultilevelDropdown.vue';
// @ts-ignore
import DbcoMultilevelDropdown, { DropdownOptionType } from './DbcoMultilevelDropdown.vue';
import { setupTest, waitForElements } from '@/utils/test';
import type { VueConstructor } from 'vue';

const realLocation = window.location;

// Provide a mock implementation of createRange for bootstrap dropdown
window.document.createRange = () =>
    ({
        setStart: () => {},
        setEnd: () => {},
        commonAncestorContainer: {
            nodeName: 'BODY',
            ownerDocument: document,
        },
    }) as any;

const createComponent = setupTest(
    (localVue: VueConstructor, propsData?: Props, scopedSlots?: { [key: string]: string }) => {
        return mount(DbcoMultilevelDropdown, {
            localVue,
            propsData,
            scopedSlots,
        });
    }
);

describe('DbcoMultilevelDropdown.vue', () => {
    afterEach(() => {
        window.location = realLocation;
    });

    it('should render the options', async () => {
        // ARRANGE
        const props: Props = {
            options: [
                {
                    type: DropdownOptionType.ITEM,
                    label: 'Item1',
                },
                {
                    type: DropdownOptionType.ITEM,
                    label: 'Item2',
                },
                {
                    type: DropdownOptionType.MENU,
                    label: 'MenuItem1',
                    options: [],
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props);
        await wrapper.find('button').trigger('click');
        // Need to wait foor bootstrap dropdown to createComponent the menu,
        // await localVue.nextTick() did not do the trick :-(
        await waitForElements(wrapper, '.dropdown-option');

        // ASSERT
        expect(wrapper.find('.dropdown-option').text()).toEqual('Item1');
        expect(wrapper.findAll('.dropdown-option')).toHaveLength(3);
    });

    it('should render the button content', () => {
        // ARRANGE
        const props: Props = {
            options: [],
        };

        // ACT
        const wrapper = createComponent(props, { default: '<b>Buttontext</b>' });

        // ASSERT
        expect(wrapper.find('button b').text()).toEqual('Buttontext');
    });

    it('should go to the href of an item when clicked', async () => {
        // ARRANGE
        const props: Props = {
            options: [
                {
                    type: DropdownOptionType.ITEM,
                    label: 'Item1',
                    href: '/test-item',
                },
                {
                    type: DropdownOptionType.ITEM,
                    label: 'Item2',
                },
                {
                    type: DropdownOptionType.MENU,
                    label: 'MenuItem1',
                    options: [],
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props);
        await wrapper.find('button').trigger('click');
        await waitForElements(wrapper, '.dropdown-option');
        await wrapper.find('.dropdown-option').trigger('click');

        // ASSERT
        expect(window.location.assign).toHaveBeenCalledWith('/test-item');
    });

    it('should call onClick of an item when clicked', async () => {
        // ARRANGE
        const clickMock = vi.fn();
        const props: Props = {
            options: [
                {
                    type: DropdownOptionType.ITEM,
                    label: 'Item1',
                    onClick: clickMock,
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props);
        await wrapper.find('button').trigger('click');
        await waitForElements(wrapper, '.dropdown-option');
        await wrapper.find('.dropdown-option').trigger('click');

        // ASSERT
        expect(clickMock).toBeCalledTimes(1);
    });

    it('should render an option using a template slot', async () => {
        // ARRANGE
        const props: Props = {
            options: [
                {
                    type: DropdownOptionType.ITEM,
                    label: '',
                    slot: 'testslot',
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props, { testslot: '<b>Item</b>' });
        await wrapper.find('button').trigger('click');
        await waitForElements(wrapper, '.dropdown-option');

        // ASSERT
        expect(wrapper.find('.dropdown-option b').text()).toEqual('Item');
    });

    it('should render the menu options on selection', async () => {
        // ARRANGE
        const props: Props = {
            options: [
                {
                    type: DropdownOptionType.MENU,
                    label: 'MenuItem1',
                    options: [
                        {
                            type: DropdownOptionType.ITEM,
                            label: 'Item1',
                        },
                    ],
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props);
        await wrapper.find('button').trigger('click');
        await waitForElements(wrapper, '.dropdown-option');
        await wrapper.find('.dropdown-option').trigger('click');

        // ASSERT
        expect(wrapper.findAll('.dropdown-option').at(0).text()).toEqual('Terug');
        expect(wrapper.findAll('.dropdown-option').at(1).text()).toEqual('Item1');
    });

    it('should navigate back when clicking "terug"', async () => {
        // ARRANGE
        const props: Props = {
            options: [
                {
                    type: DropdownOptionType.MENU,
                    label: 'MenuItem1',
                    options: [],
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props);
        await wrapper.find('button').trigger('click');
        await waitForElements(wrapper, '.dropdown-option');
        await wrapper.find('.dropdown-option').trigger('click');

        expect(wrapper.findAll('.dropdown-option').at(0).text()).toEqual('Terug');
        await wrapper.findAll('.dropdown-option').at(0).trigger('click');

        // ASSERT
        expect(wrapper.find('.dropdown-option').text()).toEqual('MenuItem1');
    });
});
