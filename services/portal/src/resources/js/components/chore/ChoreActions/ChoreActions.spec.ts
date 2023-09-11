import { fakerjs, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import ChoreActions from './ChoreActions.vue';
import { Link } from '@dbco/ui-library';

const fakeDefaultProps = {
    labelForDropAction: fakerjs.lorem.word(),
    labelForPickupAction: fakerjs.lorem.word(),
    labelForTertiaryAction: fakerjs.lorem.word(),
    labelForViewLink: fakerjs.lorem.word(),
    pickedUp: false,
    loading: false,
    viewLink: fakerjs.internet.url(),
};

const createComponent = setupTest((localVue: VueConstructor, givenProps: object = fakeDefaultProps) => {
    return shallowMount<ChoreActions>(ChoreActions, {
        localVue,
        propsData: givenProps,
    });
});

describe('ChoreActions.vue', () => {
    it('should render a button for picking up when the pickedUp prop is false', () => {
        // GIVEN a false value for the pickedUp prop
        // WHEN the actions render
        const wrapper = createComponent();

        // THEN a button for picking up should be rendered
        expect(wrapper.find('bbutton-stub').text()).toBe(fakeDefaultProps.labelForPickupAction);
    });

    it('should emit "toggle" when the button for picking up is clicked', async () => {
        // GIVEN the component renders with the pickedUp prop set to false
        const wrapper = createComponent();

        // // WHEN the button for picking up is clicked
        const callToAction = wrapper.find('bbutton-stub');
        await callToAction.trigger('click');

        // THEN it should emit "toggle"
        expect(wrapper.emitted('toggle')).toStrictEqual(expect.any(Array));
    });

    it('should render a button for dropping when the pickedUp prop is true', () => {
        // GIVEN a true value for the pickedUp prop
        const fakeProps = { ...fakeDefaultProps, ...{ pickedUp: true } };

        // WHEN the actions render
        const wrapper = createComponent({ ...fakeProps });

        // THEN a button for dropping should be rendered
        expect(wrapper.findAll('bbutton-stub').at(0).text()).toBe(fakeDefaultProps.labelForDropAction);
    });

    it('should emit "toggle" when the button for dropping is clicked', async () => {
        // GIVEN a true value for the pickedUp prop
        const fakeProps = { ...fakeDefaultProps, ...{ pickedUp: true } };
        const wrapper = createComponent({ ...fakeProps });

        // // WHEN the button for dropping is clicked
        const callToAction = wrapper.findAll('bbutton-stub').at(0);
        await callToAction.trigger('click');

        // THEN it should emit "toggle"
        expect(wrapper.emitted('toggle')).toStrictEqual(expect.any(Array));
    });

    it('should render a button for a tertiary action when the pickedUp prop is true', () => {
        // GIVEN a true value for the pickedUp prop
        const fakeProps = { ...fakeDefaultProps, ...{ pickedUp: true } };

        // WHEN the actions render
        const wrapper = createComponent({ ...fakeProps });

        // THEN a button for a tertiary action should be rendered
        expect(wrapper.findAll('bbutton-stub').at(1).text()).toBe(fakeDefaultProps.labelForTertiaryAction);
    });

    it('should emit "tertiaryAction" when the tertiary action button is clicked', async () => {
        // GIVEN a true value for the pickedUp prop
        const fakeProps = { ...fakeDefaultProps, ...{ pickedUp: true } };
        const wrapper = createComponent({ ...fakeProps });

        // // WHEN the tertiary action button is clicked
        const callToAction = wrapper.findAll('bbutton-stub').at(1);
        await callToAction.trigger('click');

        // THEN it should emit "tertiaryAction"
        expect(wrapper.emitted('tertiaryAction')).toStrictEqual(expect.any(Array));
    });

    it('should render a link when the pickedUp prop is true', () => {
        // GIVEN a true value for the pickedUp prop
        const fakeProps = { ...fakeDefaultProps, ...{ pickedUp: true } };
        const wrapper = createComponent({ ...fakeProps });

        // THEN a link should be rendered
        expect(wrapper.findComponent(Link).text()).toContain(fakeDefaultProps.labelForViewLink);
        expect(wrapper.findComponent(Link).attributes('href')).toBe(fakeDefaultProps.viewLink);
    });
});
