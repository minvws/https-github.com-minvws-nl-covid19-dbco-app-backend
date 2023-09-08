import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import type { Mock } from 'vitest';
import { computed } from 'vue';
import { createDefaultLocalVue } from '../../../test/local-vue';
import type { ControlBindings } from '../../types';
import { useErrors } from '../../composition';
import { default as ControlErrors } from './ControlErrors.vue';

vi.mock('../../composition', async () => {
    const composition = await vi.importActual<typeof import('../../composition')>('../../composition');
    return {
        ...composition,
        useErrors: vi.fn(() => computed(() => [])),
        useErrorId: vi.fn(() => computed(() => '')),
    };
});

function createComponent() {
    return mount(ControlErrors, {
        localVue: createDefaultLocalVue(),
        propsData: {
            control: {} as ControlBindings,
        },
    });
}

describe('ControlErrors.vue', () => {
    it('should be visible when there are errors', () => {
        const errorMessage = faker.lorem.sentence();
        (useErrors as Mock).mockImplementationOnce(() => computed(() => [errorMessage]));
        const wrapper = createComponent();

        expect(wrapper.isVisible()).toBe(true);
        expect(wrapper.text()).toContain(errorMessage);
    });

    it('should NOT be visible when there are NO errors', () => {
        (useErrors as Mock).mockImplementationOnce(() => computed(() => []));
        const wrapper = createComponent();
        expect(wrapper.isVisible()).toBe(false);
    });
});
