import { createLocalVue, mount } from '@vue/test-utils';
// @ts-ignore
import FormPlaceCategory from './FormPlaceCategory.vue';
import BootstrapVue from 'bootstrap-vue';
import type { UntypedWrapper } from '@/utils/test';

vi.mock('@/components/form/ts/formOptions');

describe('FormPlaceCategory.vue', () => {
    const placeCategoryImageClassMock = vi.fn((category) => {
        if (category) return `icon--category-${category}`;
        return 'icon--category-onbekend';
    });
    const localVue = createLocalVue();
    let wrapper: UntypedWrapper;
    localVue.use(BootstrapVue);

    const setWrapper = (props?: object, data?: object) => {
        wrapper = mount(FormPlaceCategory, {
            localVue,
            propsData: props,
            data: () => data,
            mocks: {
                $filters: {
                    placeCategoryImageClass: placeCategoryImageClassMock,
                },
            },
        });
    };

    it('should load the component', () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };

        const data = {
            horeca: {
                // group
                title: 'Horeca, Retail & Entertainment',
                values: {
                    // category values
                    restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                    club: { label: 'labelClub', description: 'descrClub' },
                },
            },
        };

        setWrapper(props, data);

        // ASSERT
        expect(wrapper.find('div').exists()).toBe(true);
    });

    it('should have "selectedCategory" as null if the component is created with a context.model that is false', () => {
        // ARRANGE
        const props = {
            context: {
                model: false,
            },
        };

        const data = {
            horeca: {
                // group
                title: 'Horeca, Retail & Entertainment',
                values: {
                    // category values
                    restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                    club: { label: 'labelClub', description: 'descrClub' },
                },
            },
        };

        setWrapper(props, data);

        // ASSERT
        expect(wrapper.vm.selectedCategory).toBe(null);
    });

    it('should not have "categoryDropdown" if a category does not have value', () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = { categories: { empty: { title: 'emptyCategory' } }, open: null, selectedCategory: null };
        setWrapper(props, data);

        // ASSERT
        expect(wrapper.find('button[data-testid="categoryDropdown"]').exists()).toBe(false);
    });

    it('should not have "selectedCategory" as null if the component is created with a context.model that is false', () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = { categories: { empty: {} }, open: null, selectedCategory: null };
        setWrapper(props, data);

        // ASSERT
        expect(wrapper.vm.selectedCategory).toBe(null);
    });

    it('should have "categoryDropdown" if a category contains a value', () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: {
                        // category values
                        restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                        club: { label: 'labelClub', description: 'descrClub' },
                    },
                },
            },
            open: null,
            selectedCategory: null,
        };
        setWrapper(props, data);

        // ASSERT
        expect(wrapper.find('button[data-testid="categoryDropdown"]').exists()).toBe(true);
        expect(wrapper.find('div[class="title flex-fill text-left"]').text()).toBe('Horeca, Retail & Entertainment');
    });

    it('should not have "dropdown-sub-menu" if a open is null', () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: {
                        // category values
                        restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                        club: { label: 'labelClub', description: 'descrClub' },
                    },
                },
            },
            open: null,
            selectedCategory: null,
        };
        setWrapper(props, data);

        // ASSERT
        expect(wrapper.find('div[class="dropdown-sub-menu"]').exists()).toBe(false);
    });

    it('should have "dropdown-sub-menu" (open the dropdown) if horeca group is opened on click', async () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: {
                        // category values
                        restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                        club: { label: 'labelClub', description: 'descrClub' },
                    },
                },
            },
            open: null,
            selectedCategory: null,
        };
        await setWrapper(props, data);
        await expect(wrapper.find('div[class="dropdown-sub-menu"]').exists()).toBe(false);

        // ACT
        await wrapper.find('button[data-testid="categoryDropdown"]').trigger('click');

        // ASSERT
        await expect(wrapper.find('div[class="dropdown-sub-menu"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="testIDrestaurant"]').text()).toContain('labelRestaurant');
        expect(wrapper.find('[data-testid="testIDclub"]').text()).toContain('labelClub');
        expect(wrapper.vm.open).toBe('horeca');
    });

    it('should close "dropdown-sub-menu" on click if horeca group is already open', async () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: {
                        // category values
                        restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                        club: { label: 'labelClub', description: 'descrClub' },
                    },
                },
            },
            open: 'horeca',
            selectedCategory: null,
        };
        await setWrapper(props, data);
        await expect(wrapper.find('div[class="dropdown-sub-menu"]').exists()).toBe(true);

        // ACT
        await wrapper.find('button[data-testid="categoryDropdown"]').trigger('click');

        // ASSERT
        await expect(wrapper.find('div[class="dropdown-sub-menu"]').exists()).toBe(false);
        expect(wrapper.vm.open).toBe(null);
    });

    // this gives a warning on the filter, but the test still works as intended
    it('should select a category on click', async () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: {
                        // category values
                        restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                        club: { label: 'labelClub', description: 'descrClub' },
                    },
                },
            },
            open: 'horeca',
            selectedCategory: null,
        };
        await setWrapper(props, data);

        // ACT
        await wrapper.find('[data-testid="testIDclub"]').trigger('click');

        // ASSERT
        expect(wrapper.vm.$props.context.model).toBe('club');
        expect(wrapper.vm.selectedCategory).toMatchObject({ group: 'horeca', label: 'labelClub', value: 'club' });
    });

    it('should select a group onClick if the group has no values.', async () => {
        // ARRANGE
        const props = {
            context: {
                model: {},
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                },
            },
            open: null,
            selectedCategory: null,
        };
        await setWrapper(props, data);

        // ACT
        await wrapper.find('[data-testid="groupSelector"]').trigger('click');

        // ASSERT
        expect(wrapper.vm.$props.context.model).toBe('horeca');
        expect(wrapper.vm.selectedCategory).toMatchObject({
            group: 'horeca',
            label: 'Horeca, Retail & Entertainment',
            value: 'horeca',
        });
    });

    it('should set selectedCategory if group === context.model, so if a group has already been selected', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'horeca',
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: null,
                },
            },
            open: null,
            selectedCategory: 'horeca',
        };

        setWrapper(props, data);

        // ASSERT
        // bonus minor test
        expect(wrapper.find('i[class="icon icon--xl icon--m0 icon--category-horeca"]').exists()).toBe(true);
        // actual test
        expect(wrapper.vm.selectedCategory).toMatchObject({
            group: 'horeca',
            label: 'Horeca, Retail & Entertainment',
            value: 'horeca',
        });
    });

    it('should set selectedCategory if group !== context.model, so if a category has already been selected', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'club',
            },
        };
        const data = {
            categories: {
                horeca: {
                    // group
                    title: 'Horeca, Retail & Entertainment',
                    values: {
                        // category values
                        restaurant: { label: 'labelRestaurant', description: 'descrRestaurant' },
                        club: { label: 'labelClub', description: 'descrClub' },
                    },
                },
            },
            open: null,
            selectedCategory: 'horeca',
        };

        setWrapper(props, data);

        // ASSERT
        expect(wrapper.vm.selectedCategory).toMatchObject({
            group: 'horeca',
            label: 'labelClub',
            value: 'club',
        });
    });
});
