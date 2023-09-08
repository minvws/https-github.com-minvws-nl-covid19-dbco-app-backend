import { shallowMount } from '@vue/test-utils';
import FormContextCategorySuggestions from './FormContextCategorySuggestions.vue';
import { Store } from 'vuex';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

vi.mock('@dbco/enum', async () => {
    const actual = await vi.importActual('@dbco/enum');
    return {
        ...(actual as any),
        ContextCategoryV1: {
            VALUE_restaurant: 'restaurant',
            VALUE_accomodatie_binnenland: 'accomodatie_binnenland',
        },
        contextCategoryV1Options: [
            {
                label: 'Restaurant / Café',
                value: 'restaurant',
                description: 'O.a. lunchroom, kroeg',
                group: 'horeca',
                suggestionGroup: 'horeca',
            },
            {
                label: 'Accomodatie binnenland',
                value: 'accomodatie_binnenland',
                description: 'O.a. hotel, B&B, camping, vakantiepark of -huis',
                group: 'horeca',
                suggestionGroup: 'accommodatie',
            },
        ],
        ContextCategorySuggestionGroupV1: {
            VALUE_overig: 'overig',
            VALUE_horeca: 'horeca',
        },
        contextCategorySuggestionGroupV1Options: [
            {
                label: 'Horeca',
                value: 'horeca',
                suggestions: [
                    'Welk type setting? (Dancing, bar, restaurant, etc.)',
                    'Bezoeker of medewerker?',
                    'Bij medewerker: wat is de functie?',
                    'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                    'Bij medewerker: met welke personen was er nauw contact?',
                    'Betreft het een gelegenheid binnen of buiten?',
                    'Is er gewerkt met de CoronaCheck-app?',
                    'Verplichte reservering of vrije inloop?',
                    'Hoeveel personen aan tafel?',
                    'Selfservice of bediening?',
                    'Mogelijkheid tot het houden van afstand tot medewerkers?',
                    'Zijn er contactgegevens bijgehouden van bezoekers?',
                ],
            },
            {
                label: 'Overig',
                value: 'overig',
                suggestions: [
                    'Type bijeenkomst? Duur?',
                    'Was het een georganiseerde bijeenkomst of een niet-georganiseerde bijeenkomst?',
                    'Nauw/lichamelijk contact (bijv. omhelzing)?',
                    'Zijn er mensen uit één sociale omgeving (werk/klas/opleiding) of uit meerdere sociale omgevingen aanwezig? Hoe groot was de groep?',
                    'Was het continu dezelfde groep, of was er een constante wisseling van de groep mensen?',
                    'Zijn er onregelmatigheden geweest? Was er sprake van fysiek contact?',
                ],
            },
        ],
    };
});

const createComponent = setupTest((localVue: VueConstructor, props?: object, storeData?: Store<any>) => {
    return shallowMount(FormContextCategorySuggestions, {
        localVue,
        propsData: props,
        store: storeData ?? new Store({}),
    });
});

describe('FormContextCategorySuggestions.vue', () => {
    it('should return an empty array from groupSuggestions if place is not set', () => {
        const props = {
            context: {},
        };
        const storeData = new Store({
            modules: {
                context: {
                    namespaced: true,
                    getters: {
                        place: vi.fn(() => null),
                    },
                },
            },
        });

        const wrapper = createComponent(props, storeData);

        expect(wrapper.find('li').exists()).toBe(false);
    });

    it('should use overige if place category is not set', () => {
        const props = {
            context: {},
        };
        const storeData = new Store({
            modules: {
                context: {
                    namespaced: true,
                    getters: {
                        place: vi.fn(() => ({})),
                    },
                },
            },
        });

        const wrapper = createComponent(props, storeData);

        expect(wrapper.findAll('li').at(0).text()).toBe('Type bijeenkomst? Duur?');
        expect(wrapper.findAll('li').at(-1).text()).toBe(
            'Zijn er onregelmatigheden geweest? Was er sprake van fysiek contact?'
        );
    });

    it('should use overige if category contains a value not in suggestiongroup', () => {
        const props = {
            context: {},
        };
        const storeData = new Store({
            modules: {
                context: {
                    namespaced: true,
                    getters: {
                        place: vi.fn(() => ({
                            category: 'fietsemakers',
                        })),
                    },
                },
            },
        });

        const wrapper = createComponent(props, storeData);

        expect(wrapper.findAll('li').at(0).text()).toBe('Type bijeenkomst? Duur?');
        expect(wrapper.findAll('li').at(-1).text()).toBe(
            'Zijn er onregelmatigheden geweest? Was er sprake van fysiek contact?'
        );
    });

    it('should use correct suggestions from suggestiongroup', () => {
        const props = {
            context: {},
        };
        const storeData = new Store({
            modules: {
                context: {
                    namespaced: true,
                    getters: {
                        place: vi.fn(() => ({
                            category: 'restaurant',
                        })),
                    },
                },
            },
        });

        const wrapper = createComponent(props, storeData);

        expect(wrapper.findAll('li').at(0).text()).toBe('Welk type setting? (Dancing, bar, restaurant, etc.)');
        expect(wrapper.findAll('li').at(-1).text()).toBe('Zijn er contactgegevens bijgehouden van bezoekers?');
    });
});
