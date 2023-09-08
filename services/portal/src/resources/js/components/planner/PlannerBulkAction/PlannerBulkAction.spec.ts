import { setupTest } from '@/utils/test';
import { faker } from '@faker-js/faker';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue/types/vue';

import PlannerBulkAction from './PlannerBulkAction.vue';

const selected = [faker.string.uuid(), faker.string.uuid(), faker.string.uuid()];

const defaultProps = {
    archiveable: false,
    assignable: false,
    selected,
    staleSince: faker.date.past().toString(),
};

const createComponent = setupTest((localVue: VueConstructor, props: object = {}) => {
    return shallowMount(PlannerBulkAction, {
        localVue,
        propsData: {
            ...defaultProps,
            ...props,
        },
        stubs: {
            BButton: true,
            BDropdown: true,
            DbcoAssignDropdown: true,
        },
    });
});

describe('PlannerBulkAction.vue', () => {
    it('should be visible', () => {
        const wrapper = createComponent();

        expect(wrapper.find('.bulk-actionbar-wrapper').exists()).toBe(true);
    });

    it('should show Archive Button if archiveable prop is set to true', () => {
        const wrapper = createComponent({ archiveable: true });

        const button = wrapper.findComponent({ name: 'BButton' });

        expect(button.exists()).toBe(true);
    });

    it('should NOT show Archive Button if archiveable prop is set to false', () => {
        const wrapper = createComponent({ archiveable: false });

        const button = wrapper.findComponent({ name: 'BButton' });

        expect(button.exists()).toBe(false);
    });

    it('should show assignment dropdown if assignable prop is set to true', () => {
        const wrapper = createComponent({ assignable: true });

        const button = wrapper.findComponent({ name: 'DbcoAssignDropdown' });

        expect(button.exists()).toBe(true);
    });

    it('should NOT show assignment dropdown if assignable prop is set to false', () => {
        const wrapper = createComponent({ assignable: false });

        const button = wrapper.findComponent({ name: 'DbcoAssignDropdown' });

        expect(button.exists()).toBe(false);
    });

    it('should emit onArchive when archive button is clicked', async () => {
        const wrapper = createComponent({ archiveable: true });

        const archiveBtn = wrapper.find('.archive-button');

        await archiveBtn.trigger('click');

        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('onArchive')).toBeTruthy();
    });

    it('should emit onAssign when assign method is called', async () => {
        const wrapper = createComponent({ assignable: true });

        const assignment = {
            cases: ['801fec6b-faa8-4784-a65b-591c3bfd3f9b', '9ece2ab4-4d70-41e1-9c32-0f326d7a655a'],
            assignedUserUuid: '00000000-0000-0000-0000-000000000001',
            option: {
                type: 'option',
                label: 'Demo GGD1 Gebruiker',
                isSelected: false,
                isEnabled: true,
                assignmentType: 'user',
                assignment: {
                    assignedUserUuid: '00000000-0000-0000-0000-000000000001',
                },
            },
        };

        await wrapper.vm.assigned(assignment);

        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('onAssign')).toBeTruthy();
    });

    it('should emit onUpdatePriority when priority option is clicked', async () => {
        const wrapper = createComponent();

        const dropdown = wrapper.findComponent({ name: 'BDropdown' });
        await dropdown.trigger('click');

        wrapper.findByTestId('dropdown-item-priority-0').vm.$emit('click');
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('onUpdatePriority')).toBeTruthy();
    });

    it('should emit onClear when close button is clicked', async () => {
        const wrapper = createComponent();

        const archiveBtn = wrapper.find('.close-button');

        await archiveBtn.trigger('click');
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('onClear')).toBeTruthy();
    });
});
