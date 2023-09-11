import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import type { Mock } from 'vitest';
import { computed, inject } from 'vue';
import { createDefaultLocalVue } from '../../../test/local-vue';
import type { ControlBindings } from '../../types';
import { useId } from '../../composition';
import { default as ControlLabel } from './ControlLabel.vue';

vi.mock('../../composition', async () => {
    const composition = await vi.importActual<typeof import('../../composition')>('../../composition');
    return {
        ...composition,
        useId: vi.fn(() => computed(() => faker.lorem.word())),
    };
});

type Props = {
    control: Pick<ControlBindings, 'label' | 'required'>;
};

function createComponent(propsData: Props) {
    return mount(ControlLabel, {
        localVue: createDefaultLocalVue(),
        propsData: {
            ...propsData,
            control: propsData.control as ControlBindings,
        },
    });
}

describe('ControlLabel.vue', () => {
    beforeAll(() => {
        (inject as Mock).mockImplementation((key: symbol | string) => {
            switch (key.toString()) {
                case 'jsonforms':
                    return { jsonforms: {} };
                case 'Symbol(i18n-translate)':
                    return vi.fn(() => '(Verplicht)');
            }
            return undefined;
        });
    });

    it('should contain the control label and have a for id attribute', () => {
        const label = faker.lorem.sentence();
        const controlId = faker.lorem.word();
        (useId as Mock).mockImplementationOnce(() => computed(() => controlId));

        const wrapper = createComponent({ control: { label, required: false } });

        expect(wrapper.attributes('for')).toBe(controlId);
        expect(wrapper.text()).toBe(label);
    });

    it('should have a flag if the value is required', () => {
        const label = faker.lorem.sentence();
        const wrapper = createComponent({ control: { label, required: true } });
        expect(wrapper.text()).toContain('(Verplicht)');
    });

    it('should NOT be visible if a control does not have a label', () => {
        const wrapper = createComponent({ control: { label: '', required: true } });
        expect(wrapper.isVisible()).toBe(false);
    });
});
