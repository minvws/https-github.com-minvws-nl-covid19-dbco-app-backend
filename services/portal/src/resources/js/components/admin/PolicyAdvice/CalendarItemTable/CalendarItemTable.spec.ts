import type { VueConstructor } from 'vue';
import { mount } from '@vue/test-utils';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import CalendarItemTable from './CalendarItemTable.vue';
import { fakeCalendarItem, fakePolicyVersion } from '@/utils/__fakes__/admin';
import { Button, Tbody, Td, Tr } from '@dbco/ui-library';
import { adminApi } from '@dbco/portal-api';
import { PolicyPersonTypeV1, PolicyVersionStatusV1, CalendarItemV1, calendarPeriodColorV1Options } from '@dbco/enum';
import { BDropdown, BDropdownItem } from 'bootstrap-vue';

vi.mock('@/router/router');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(CalendarItemTable, {
        localVue,
        propsData: {
            personType: PolicyPersonTypeV1.VALUE_index,
            versionStatus: PolicyVersionStatusV1.VALUE_draft,
            versionUuid: fakerjs.string.uuid(),
            ...props,
        },
        stubs: { Backdrop: true, LastUpdated: true },
    });
});

describe('CalendarItemTable.vue', () => {
    it('should show table with correct number of items', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() =>
            Promise.resolve([fakeCalendarItem(), fakeCalendarItem()])
        );
        const wrapper = createComponent();
        await flushCallStack();
        const tableBody = wrapper.findComponent(Tbody);
        // 1 extra because of "nieuw item" button
        expect(tableBody.findAllComponents(Tr).length).toBe(3);
    });

    it('should show a message when no items are found', () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([]));
        const wrapper = createComponent();
        expect(wrapper.text()).toContain('Geen kalender items gevonden');
    });

    it('should show correct list of options in color dropdown', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        const wrapper = createComponent();
        await flushCallStack();
        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(BDropdown);
        const options = dropdown.findAllComponents(BDropdownItem);
        expect(options.at(0).text()).toContain(calendarPeriodColorV1Options.light_red);
        expect(options.at(options.length - 1).text()).toContain(calendarPeriodColorV1Options.light_pink);
    });

    it('should update item when its name is changed', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementation(() => Promise.resolve([fakeCalendarItem()]));
        const givenLabel = fakerjs.lorem.word();
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updateCalendarItem')
            .mockImplementationOnce(() => Promise.resolve(fakeCalendarItem()));

        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const nameInput = tableBodyRow.findAll('.formulate-input').at(0);
        nameInput.vm.$emit(
            'change',
            {
                stopPropagation: vi.fn(),
                target: {
                    value: givenLabel,
                },
            },
            givenLabel
        );
        await flushCallStack();
        expect(spyOnUpdate).toHaveBeenCalledOnce();
    });

    it('should show error when label update api call returns an error', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        const expectedColorError = ['Selecteer een kleur'];
        const expectedLabelError = ['Geef het kalender item een naam'];
        const spyOnUpdate = vi.spyOn(adminApi, 'updateCalendarItem').mockImplementationOnce(() =>
            Promise.reject({
                response: {
                    data: {
                        errors: {
                            color: expectedColorError,
                            label: expectedLabelError,
                        },
                    },
                },
            })
        );

        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const nameInput = tableBodyRow.findAll('.formulate-input').at(0);
        await nameInput.vm.$emit('change', { stopPropagation: vi.fn() });
        await flushCallStack();
        expect(spyOnUpdate).toHaveBeenCalledOnce();
        const formError = nameInput.find('.formulate-input-error');
        expect(formError.text()).toContain(expectedLabelError);
    });

    it('should disable interactive elements when the disabled prop is set to true', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        const wrapper = createComponent({ disabled: true });
        await flushCallStack();
        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(BDropdown);
        expect(dropdown.find('button').attributes('disabled')).toBe('disabled');
    });

    it('should show a modal when a change is made to a version with status active soon', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        const wrapper = createComponent({
            versionStatus: PolicyVersionStatusV1.VALUE_active_soon,
        });
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const nameInput = tableBodyRow.findAll('.formulate-input').at(0);
        await nameInput.vm.$emit('change', { stopPropagation: vi.fn() });
        await flushCallStack();

        const modals = wrapper.findAll('backdrop-stub');
        expect(modals.at(1).attributes('isopen')).toBe('true');
    });

    it('should handle pending updates when the status change warning modal is submitted', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        const wrapper = createComponent({
            versionStatus: PolicyVersionStatusV1.VALUE_active_soon,
        });
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const nameInput = tableBodyRow.findAll('.formulate-input').at(0);
        await nameInput.vm.$emit('change', { stopPropagation: vi.fn() });
        await flushCallStack();

        const modals = wrapper.findAll('backdrop-stub');
        const okButton = modals.at(1).findAllComponents(Button).at(2);
        await okButton.trigger('click');

        expect(spyOnUpdate).toHaveBeenCalledOnce();
    });

    it('should open the creation modal after the status change warning modal is submitted', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        const wrapper = createComponent({
            versionStatus: PolicyVersionStatusV1.VALUE_active_soon,
        });
        await flushCallStack();

        const tableBodyRows = wrapper.findComponent(Tbody).findAllComponents(Tr);
        const lastRow = tableBodyRows.at(tableBodyRows.length - 1);
        const newItemButton = lastRow.find('button');
        await newItemButton.trigger('click');

        const modals = wrapper.findAll('backdrop-stub');
        const okButton = modals.at(1).findAllComponents(Button).at(2);
        await okButton.trigger('click');
        await flushCallStack();

        const modals2 = wrapper.findAll('backdrop-stub');
        expect(modals2.at(0).attributes('isopen')).toBe('true');
    });

    it('should open the delete modal after the status change warning modal is submitted', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() =>
            Promise.resolve([fakeCalendarItem({ isDeletable: true })])
        );
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        const wrapper = createComponent({
            versionStatus: PolicyVersionStatusV1.VALUE_active_soon,
        });
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const rowColumns = tableBodyRow.findAllComponents(Td);
        const lastColumn = rowColumns.at(rowColumns.length - 1);
        const deleteButton = lastColumn.find('button');
        await deleteButton.trigger('click');

        const modals = wrapper.findAll('backdrop-stub');
        const okButton = modals.at(1).findAllComponents(Button).at(2);
        await okButton.trigger('click');
        await flushCallStack();

        const modals2 = wrapper.findAll('backdrop-stub');
        expect(modals2.at(2).attributes('isopen')).toBe('true');
    });

    it('should delete item when the delete modal is submitted', async () => {
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementation(() =>
            Promise.resolve([fakeCalendarItem(), fakeCalendarItem({ isDeletable: true })])
        );
        const spyOnDelete = vi
            .spyOn(adminApi, 'deleteCalendarItem')
            .mockImplementationOnce(() => Promise.resolve(fakeCalendarItem()));
        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(1);
        const rowColumns = tableBodyRow.findAllComponents(Td);
        const lastColumn = rowColumns.at(rowColumns.length - 1);
        const deleteButton = lastColumn.find('button');
        await deleteButton.trigger('click');

        const modals = wrapper.findAll('backdrop-stub');
        const okButton = modals.at(2).findAllComponents(Button).at(2);
        await okButton.trigger('click');
        await flushCallStack();

        expect(spyOnDelete).toHaveBeenCalledOnce();
    });

    it('should create item through creation modal', async () => {
        const givenLabel = fakerjs.lorem.word();
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        const spyOnCreate = vi
            .spyOn(adminApi, 'createCalendarItem')
            .mockImplementationOnce(() => Promise.resolve(fakeCalendarItem()));
        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRows = wrapper.findComponent(Tbody).findAllComponents(Tr);
        const lastRow = tableBodyRows.at(tableBodyRows.length - 1);
        const newItemButton = lastRow.find('button');
        await newItemButton.trigger('click');

        const modals = wrapper.findAll('backdrop-stub');
        const createModal = modals.at(0);
        const nameInput = createModal.findAll('.formulate-input').at(0);
        nameInput.vm.$emit(
            'change',
            {
                stopPropagation: vi.fn(),
                target: {
                    value: givenLabel,
                },
            },
            givenLabel
        );
        const dropdownItems = createModal.findAllComponents({ name: 'BDropdownItem' });
        dropdownItems.at(0).vm.$emit('click');
        const itemRadio = createModal.findAll('.formulate-input').at(4);
        itemRadio.vm.$emit(
            'input',
            {
                stopPropagation: vi.fn(),
            },
            CalendarItemV1.VALUE_point
        );
        await flushCallStack();

        const okButton = createModal.findAllComponents(Button).at(2);
        await okButton.trigger('click');
        await flushCallStack();

        expect(spyOnCreate).toHaveBeenCalledOnce();
    });

    it('should show api call errors in creation modal', async () => {
        const expectedColorError = ['Selecteer een kleur'];
        const expectedPersonTypeError = ['Selecteer index of contact'];
        const expectedItemTypeError = ['Selecteer een periode of dag'];
        const expectedLabelError = ['Geef het kalender item een naam'];
        vi.spyOn(adminApi, 'getCalendarItems').mockImplementationOnce(() => Promise.resolve([fakeCalendarItem()]));
        vi.spyOn(adminApi, 'createCalendarItem').mockImplementationOnce(() =>
            Promise.reject({
                response: {
                    data: {
                        errors: {
                            color: expectedColorError,
                            personType: expectedPersonTypeError,
                            itemType: expectedItemTypeError,
                            label: expectedLabelError,
                        },
                    },
                },
            })
        );
        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRows = wrapper.findComponent(Tbody).findAllComponents(Tr);
        const lastRow = tableBodyRows.at(tableBodyRows.length - 1);
        const newItemButton = lastRow.find('button');
        await newItemButton.trigger('click');

        const modals = wrapper.findAll('backdrop-stub');
        const createModal = modals.at(0);

        const okButton = createModal.findAllComponents(Button).at(2);
        await okButton.trigger('click');
        await flushCallStack();

        expect(createModal.text()).toContain(expectedColorError[0]);
        expect(createModal.text()).toContain(expectedPersonTypeError[0]);
        expect(createModal.text()).toContain(expectedItemTypeError[0]);
        expect(createModal.text()).toContain(expectedLabelError[0]);
    });
});
