import { shallowMount } from '@vue/test-utils';
import ActionDropdown from './ActionDropdown.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';

const createComponent = setupTest((localVue: VueConstructor, caseUpdates?: Partial<PlannerCaseListItem>) => {
    return shallowMount(ActionDropdown, {
        localVue,
        propsData: {
            item: {
                ...fakePlannerCaseListItem,
                ...caseUpdates,
            },
        },
        stubs: {
            BDropdown: true,
            BDropdownItem: true,
            BDropdownDivider: true,
        },
    });
});

describe('ActionDropdown.vue', () => {
    it('should not render dropdown when not closable, deletable, editable or reopenable', () => {
        // GIVEN that a planner is not closable, deletable, editable or reopenable
        const plannerCase: Partial<PlannerCaseListItem> = {
            isDeletable: false,
            isEditable: false,
            isClosable: false,
            isReopenable: false,
            canChangeOrganisation: false,
        };
        // WHEN the component renders
        const wrapper = createComponent(plannerCase);
        // THEN it should not render a dropdown
        expect(wrapper.find('bdropdown-stub').exists()).toBe(false);
    });

    it('should render close option when closable', () => {
        // GIVEN that a planner is closeable
        const plannerCase: Partial<PlannerCaseListItem> = { isClosable: true, canChangeOrganisation: true };
        // WHEN the component renders
        const wrapper = createComponent(plannerCase);
        // THEN it renders a close option
        const items = wrapper.findAll('bdropdownitem-stub');
        // AND it renders the proper text in the option
        expect(items.at(1).text()).toBe('Sluiten');
    });

    it('should emit close event when close option is clicked', () => {
        // GIVEN the component renders a close option
        const plannerCase: Partial<PlannerCaseListItem> = { isClosable: true, canChangeOrganisation: true };
        const wrapper = createComponent(plannerCase);
        const items = wrapper.findAll('bdropdownitem-stub');
        // WHEN this option is clicked
        items.at(1).vm.$emit('click');
        // THEN the component emits 'close'
        expect(wrapper.emitted().close).toBeTruthy();
    });

    it('should render reopen option when reopenable', () => {
        // GIVEN that a planner is reopenable
        const plannerCase: Partial<PlannerCaseListItem> = { isReopenable: true, canChangeOrganisation: true };
        // WHEN the component renders
        const wrapper = createComponent(plannerCase);
        // THEN it renders a reopen option
        const items = wrapper.findAll('bdropdownitem-stub');
        // AND it renders the proper text in the option
        expect(items.at(1).text()).toBe('Heropenen');
    });

    it('should emit reopen event when reopen option is clicked', () => {
        // GIVEN the component renders a reopen option
        const plannerCase: Partial<PlannerCaseListItem> = { isReopenable: true, canChangeOrganisation: true };
        const wrapper = createComponent(plannerCase);
        const items = wrapper.findAll('bdropdownitem-stub');
        // WHEN this option is clicked
        items.at(1).vm.$emit('click');
        // THEN the component emits 'reopen'
        expect(wrapper.emitted().reopen).toBeTruthy();
    });

    it('should render edit option when editable', () => {
        // GIVEN that a planner is editable
        const plannerCase: Partial<PlannerCaseListItem> = { isEditable: true, canChangeOrganisation: true };
        // WHEN the component renders
        const wrapper = createComponent(plannerCase);
        // THEN it renders a edit option
        const items = wrapper.findAll('bdropdownitem-stub');
        // AND it renders the proper text in the option
        expect(items.at(0).text()).toBe('Details Wijzigen');
    });

    it('should emit edit event when edit option is clicked', () => {
        // GIVEN the component renders an edit option
        const plannerCase: Partial<PlannerCaseListItem> = { isEditable: true, canChangeOrganisation: true };
        const wrapper = createComponent(plannerCase);
        const items = wrapper.findAll('bdropdownitem-stub');
        // WHEN this option is clicked
        items.at(0).vm.$emit('click');
        // THEN the component emits 'edit'
        expect(wrapper.emitted().edit).toBeTruthy();
    });

    it('should render delete option when deletable', () => {
        // GIVEN that a planner is deletable
        const plannerCase: Partial<PlannerCaseListItem> = { isDeletable: true, canChangeOrganisation: true };
        // WHEN the component renders
        const wrapper = createComponent(plannerCase);
        // THEN it renders a delete option
        const items = wrapper.findAll('bdropdownitem-stub');
        // AND it renders the proper text in the option
        expect(items.at(1).text()).toBe('Verwijderen');
    });

    it('should emit delete event when delete option is clicked', () => {
        // GIVEN the component renders a delete option
        const plannerCase: Partial<PlannerCaseListItem> = { isDeletable: true, canChangeOrganisation: true };
        const wrapper = createComponent(plannerCase);
        const items = wrapper.findAll('bdropdownitem-stub');
        // WHEN this option is clicked
        items.at(1).vm.$emit('click');
        // THEN the component emits 'delete'
        expect(wrapper.emitted().delete).toBeTruthy();
    });
});
