import { createLocalVue, shallowMount } from '@vue/test-utils';

import DbcoPhaseDropdown from './DbcoPhaseDropdown.vue';
import type { Mock } from 'vitest';
import TypingHelpers from '@/plugins/typings';
import { bcoPhaseV1Options } from '@dbco/enum';
import { flushCallStack } from '@/utils/test';
import { userCanEdit } from '@/utils/interfaceState';

vi.mock('@dbco/portal-api/client/case.api', () => ({
    updateBCOPhase: vi.fn(() => Promise.resolve()),
}));

vi.mock('@/utils/interfaceState');

const defaultProps = {
    bcoPhase: 'none',
    cases: ['12324869265'],
};

const stubs = {
    BDropdown: true,
    BDropdownItem: true,
};

describe('DbcoPhaseDropdown.vue', () => {
    const localVue = createLocalVue();
    localVue.use(TypingHelpers);

    const setWrapper = (props?: object) => {
        return shallowMount<DbcoPhaseDropdown>(DbcoPhaseDropdown, {
            localVue,
            propsData: { ...defaultProps, ...props },
            stubs,
        });
    };

    it('should render alphabetically sorted bcoPhaseV1Options as dropdown items', () => {
        // GIVEN the component renders the dropdown
        const wrapper = setWrapper();
        const items = wrapper.findAll('bdropdownitem-stub');

        // THEN the dropdown items should be alphabetically sorted bcoPhaseV1Options
        const sortedPhases = Object.entries(bcoPhaseV1Options).sort(([phaseA], [phaseB]) => (phaseA < phaseB ? -1 : 1));
        items.wrappers.forEach((item, index) => {
            expect(item.html()).toContain(sortedPhases[index][1]);
        });
    });

    it('should render fallback label when no bcoPhase is given through prop', () => {
        // GIVEN the component renders the dropdown without bcoPhase
        const wrapper = setWrapper({
            bcoPhase: undefined,
            cases: ['131234234234', '132423525243'],
        });
        const dropdown = wrapper.find('bdropdown-stub');

        // THEN the dropdown should use fallback label
        expect(dropdown.html()).toContain('Fase');
    });

    it('should emit phaseChanged event when phase is changed', async () => {
        // GIVEN the component renders the dropdown
        const wrapper = setWrapper();
        const item = wrapper.find('bdropdownitem-stub');
        // WHEN an option is clicked
        item.vm.$emit('click');
        await flushCallStack();

        // THEN the component emits 'phaseChanged'
        expect(wrapper.emitted().phaseChanged).toBeTruthy();
    });
    it('should  disable the Phase Dropdown when userCanEdit is false', () => {
        (userCanEdit as Mock).mockImplementation(() => false);
        const wrapper = setWrapper();

        expect(wrapper.find('#dbco-phase-dropdown').attributes().disabled).toBe('true');
    });
});
