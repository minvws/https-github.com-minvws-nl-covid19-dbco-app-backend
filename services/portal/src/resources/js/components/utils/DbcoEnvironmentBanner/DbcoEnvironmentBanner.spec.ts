import { createLocalVue, shallowMount } from '@vue/test-utils';
import DbcoEnvironmentBanner from './DbcoEnvironmentBanner.vue';

describe('DbcoEnvironmentBanner.vue', () => {
    const localVue = createLocalVue();

    const setWrapper = (props?: object) =>
        shallowMount(DbcoEnvironmentBanner, {
            localVue,
            propsData: props,
        });

    it.each([
        ['ontwikkelomgeving', 'banner--blue', 'development'],
        ['acceptatieomgeving', 'banner--green', 'acceptance'],
        ['testomgeving', 'banner--purple', 'test'],
        ['trainingsomgeving', 'banner--yellow', 'training'],
    ])(
        '%#: should show banner with text "%s" and class "%s" for environment "%s"',
        (expectedBannerText, expectedBannerClass, environment) => {
            const props = { environment };

            const wrapper = setWrapper(props);

            expect(wrapper.find(`.${expectedBannerClass}`).html()).toContain(expectedBannerText);
        }
    );
});
