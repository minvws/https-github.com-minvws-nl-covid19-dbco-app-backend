import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { BFormInput } from 'bootstrap-vue';
import { BDropdown } from 'bootstrap-vue';
import type { VueConstructor } from 'vue';
import DbcoFilter from './DbcoFilter.vue';

const options = [
    { value: '1', label: 'User 1 a' },
    { value: '2', label: 'User 2 b' },
];

const defaultProps = {
    label: 'Filter',
    type: 'user',
    options: options,
};

const createComponent = setupTest(
    (localVue: VueConstructor, props: object = defaultProps, data: object = {}, rootModel: object = {}) => {
        return shallowMount(DbcoFilter, {
            localVue,
            propsData: { ...defaultProps, ...props },
            data: () => data,
            stubs: { BDropdown: true, BFormInput: true, BInputGroupAppend: true, BInputGroup: true },
            provide: {
                rootModel: () => rootModel,
            },
        });
    }
);
describe('DBCOFilter', () => {
    it('should render all filter options labels', () => {
        const wrapper = createComponent({ options });
        const renderedOptions = wrapper.findAll('.dropdown-option');

        expect(renderedOptions.length).toBe(2);
        expect(renderedOptions.at(0).text()).toBe('User 1 a');
        expect(renderedOptions.at(1).text()).toBe('User 2 b');
    });

    it('should show search input with placeholder when searchable prop is true', () => {
        const wrapper = createComponent({ searchable: true, searchPlaceholder: 'Zoek medewerker' });
        const searchInput = wrapper.find('BFormInput-stub');

        expect(searchInput.exists()).toBeTruthy();
        expect(searchInput.attributes('placeholder')).toBe('Zoek medewerker');
    });

    it('should filter options based on searchInput', () => {
        const data = { searchString: 'User 2' };
        const wrapper = createComponent({ options, searchable: true }, data);

        const foundOptions = wrapper.findAll('.dropdown-option');
        expect(foundOptions.length).toBe(1);
        expect(foundOptions.at(0).text()).toBe('User 2 b');
    });

    it('should close dropdown when selecting an option', async () => {
        const dropdownRef: Partial<BDropdown> = {
            hide: vi.fn(),
        };
        const wrapper = createComponent();

        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;
        await wrapper.find('.dropdown-option').trigger('click');

        expect(dropdownRef.hide).toBeCalled();
    });

    it('should reset search query when closing dropdown', async () => {
        const dropdownRef: Partial<BDropdown> = {
            hide: vi.fn(),
        };
        const wrapper = createComponent({ searchable: true }, { searchString: 'User 2' });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown; // dropdown ref is needed to 'hide' dropdown without throwing errors

        await wrapper.find('.dropdown-option').trigger('click');
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.searchString).toBe(null);
    });

    it('should focus search field when opening dropdown is shown', () => {
        const inputRef: Partial<BFormInput> = {
            focus: vi.fn(),
        };
        const wrapper = createComponent({ searchable: true });
        wrapper.vm.$refs.searchInput = inputRef as BFormInput;

        const dropdown = wrapper.findComponent(BDropdown);
        dropdown.vm.$emit('shown');

        expect(inputRef.focus).toBeCalled();
    });

    it('should render not show search input when not searchable', () => {
        const wrapper = createComponent();

        expect(wrapper.find('BFormInput-stub').exists()).toBeFalsy();
    });
});
