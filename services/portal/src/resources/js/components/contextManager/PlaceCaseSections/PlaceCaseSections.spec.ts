import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, setupTest } from '@/utils/test';
import i18n from '@/i18n/index';

import PlaceCaseSections from './PlaceCaseSections.vue';

const createComponent = setupTest((localVue: VueConstructor, givenSections: Array<string> = []) => {
    return shallowMount<PlaceCaseSections>(PlaceCaseSections, {
        localVue,
        i18n,
        propsData: {
            sections: givenSections,
        },
    });
});

describe('PlaceCaseSections.vue', () => {
    it('should render a fallback when there are no sections', () => {
        // GIVEN an empty array of sections through props
        const givenSections: Array<string> = [];

        // WHEN the component is rendered
        const wrapper = createComponent(givenSections);

        // THEN it should render the sections separated by commas
        const sections = wrapper.find('span');
        expect(sections.text()).toBe('-');
    });

    it('should render sections separated by commas when given less than 3', () => {
        // GIVEN an array of less than 3 sections through props
        const givenSections = fakerjs.custom.typedArray<string>(fakerjs.location.secondaryAddress(), 1, 3);

        // WHEN the component is rendered
        const wrapper = createComponent(givenSections);

        // THEN it should render the sections separated by commas
        const sections = wrapper.find('span');
        const expectedFormatting = givenSections.join(', ');
        expect(sections.text()).toBe(expectedFormatting);
    });

    it('should render the first 3 sections separated by commas AND trigger to show all when given more than 3', () => {
        // GIVEN an array of more than 3 sections through props
        const givenSections = fakerjs.custom.typedArray<string>(fakerjs.location.secondaryAddress(), 4, 10);

        // WHEN the component is rendered
        const wrapper = createComponent(givenSections);

        // THEN it should render the first 3 sections separated by commas
        const sections = wrapper.find('span');
        const expectedFormatting = givenSections.slice(0, 3).join(', ');
        expect(sections.text()).toContain(expectedFormatting);

        // AND a trigger to show all sections
        const trigger = wrapper.find('bbutton-stub');
        expect(trigger.exists()).toBe(true);
        expect(trigger.text()).toBe(
            i18n.t('components.placeCasesTable.hints.show_all', { count: givenSections.length })
        );
    });

    it('should show all sections after click on show all trigger', async () => {
        // GIVEN an array of more than 3 sections through props
        const givenSections = fakerjs.custom.typedArray<string>(fakerjs.location.secondaryAddress(), 4, 10);
        const wrapper = createComponent(givenSections);

        // WHEN the component is rendered
        const trigger = wrapper.find('bbutton-stub');
        await trigger.trigger('click');

        // THEN it should render all sections separated by commas
        const sections = wrapper.find('span');
        const expectedFormatting = givenSections.join(', ');
        expect(sections.text()).toContain(expectedFormatting);
    });
});
