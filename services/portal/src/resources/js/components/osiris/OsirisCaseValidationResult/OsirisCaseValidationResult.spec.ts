import { fakerjs, setupTest } from '@/utils/test';
import type { CaseValidationMessages } from '@dbco/portal-api/case.dto';
import { mount } from '@vue/test-utils';

import type { VueConstructor } from 'vue';
import OsirisCaseValidationResult from './OsirisCaseValidationResult.vue';

type Props = {
    validationMessages: CaseValidationMessages;
};

const createComponent = setupTest((localVue: VueConstructor, props: Props) => {
    return mount(OsirisCaseValidationResult, {
        localVue,
        propsData: props,
        stubs: {
            Collapse: true,
            BModal: true,
        },
    });
});

describe('OsirisCaseValidationResult.vue', () => {
    it.each([
        [
            {
                fatal: [fakerjs.lorem.word()],
                warning: [],
                notice: [],
            } as CaseValidationMessages,
            'Wanneer de verplichte vragen niet ingevuld worden, kan er geen Osiris melding worden gedaan.',
        ],
        [
            {
                fatal: [],
                warning: [fakerjs.lorem.word()],
                notice: [],
            } as CaseValidationMessages,
            'Wanneer de verplichte vragen niet ingevuld worden, kan er geen Osiris melding worden gedaan.',
        ],
        [
            {
                fatal: [],
                warning: [fakerjs.lorem.word()],
                notice: [fakerjs.lorem.word()],
            } as CaseValidationMessages,
            'Wanneer de verplichte vragen niet ingevuld worden, kan er geen Osiris melding worden gedaan.',
        ],
        [
            {
                fatal: [],
                warning: [],
                notice: [fakerjs.lorem.word()],
            } as CaseValidationMessages,
            'Controleer de vragen, en vul deze aan waar nodig.',
        ],
        [
            {
                fatal: [],
                warning: [],
                notice: [],
            } as CaseValidationMessages,
            'Controleer de vragen, en vul deze aan waar nodig.',
        ],
    ])('should show correct title when error messages are present', (validationMessages, expectedDescription) => {
        const wrapper = createComponent({ validationMessages });

        expect(wrapper.text()).toContain(expectedDescription);
    });
});
