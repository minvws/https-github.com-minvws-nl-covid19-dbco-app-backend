import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, mount } from '@vue/test-utils';

import BootstrapVue from 'bootstrap-vue';
import i18n from '@/i18n/index';

import NavigatorInput from '@/components/supervision/NavigatorInput/NavigatorInput.vue';
import { faker } from '@faker-js/faker';

describe('NavigatorInput.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<NavigatorInput>;

    localVue.use(BootstrapVue);

    const setWrapper = () => {
        wrapper = mount(NavigatorInput, {
            localVue,
            i18n,
        });
    };

    it('should render a numeric input for caseId', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.find('input[type="text"]').exists()).toBeTruthy();
    });

    it('should render a translated hint for more input', async () => {
        // ARRANGE
        setWrapper();
        await wrapper.find('input[type="text"]').setValue('123456');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(wrapper.find('#search-hint').text()).toBe('Voer een geldig casenummer in');
    });

    it('should hide a translated hint when input is enough', async () => {
        // ARRANGE
        setWrapper();
        await wrapper.find('input[type="text"]').setValue('12345678');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(wrapper.find('#search-hint').exists()).toBeFalsy();
    });

    it('should not render a translated hint for more input when input is cleared', async () => {
        // ARRANGE
        setWrapper();
        await wrapper.find('input[type="text"]').setValue('123456');
        await wrapper.vm.$nextTick();
        await wrapper.find('input[type="text"]').setValue('');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(wrapper.find('#search-hint').exists()).toBeFalsy();
    });

    it('should not render a translated hint for more input when input is cleared', async () => {
        // ARRANGE
        setWrapper();
        await wrapper.find('input[type="text"]').setValue('123456');
        await wrapper.vm.$nextTick();
        await wrapper.find('input[type="text"]').setValue('');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(wrapper.find('#search-hint').exists()).toBeFalsy();
    });

    it('should add dashes between 3rd/4th character and 6th/7th character when input string is 9 characters long', async () => {
        setWrapper();

        // Generate a random number with 9 digits
        const randomInt = faker.number.int({ min: 100000000, max: 999999999 });
        await wrapper.find('input[type="text"]').setValue(randomInt);

        await wrapper.vm.$nextTick();

        const inputField: HTMLInputElement = wrapper.find('input[type="text"]').element as HTMLInputElement;
        const randomIntString = String(randomInt);
        expect(inputField.value).toEqual(
            `${randomIntString.substr(0, 3)}-${randomIntString.substr(3, 3)}-${randomIntString.substr(6, 3)}`
        );
    });

    it('should remove special characters from input', async () => {
        setWrapper();
        // A string which contains special characters will be stripped from them
        // Special characters are all characters except for integers and a-z / A-Z
        const stringWithSpecialCharacters = '()3242$#^9857%#&*^';
        await wrapper.find('input[type="text"]').setValue(stringWithSpecialCharacters);

        await wrapper.vm.$nextTick();

        const inputField: HTMLInputElement = wrapper.find('input[type="text"]').element as HTMLInputElement;
        expect(inputField.value).toEqual('32429857');
    });

    it('should remove special characters from input', async () => {
        setWrapper();
        // A string which contains special characters will be stripped from them
        // Special characters are all characters except for integers and a-z / A-Z
        const stringWithSpecialCharacters = '()123$#^456%78#&*^';
        await wrapper.find('input[type="text"]').setValue(stringWithSpecialCharacters);

        await wrapper.vm.$nextTick();

        const inputField: HTMLInputElement = wrapper.find('input[type="text"]').element as HTMLInputElement;
        expect(inputField.value).toEqual('12345678');
    });

    it('should add dashes to a num/char string with the length of nine after the third and sixth character', async () => {
        setWrapper();
        // A string which contains special characters will be stripped from them
        // Special characters are all characters except for integers and a-z / A-Z
        const stringWithSpecialCharacters = 'ABC123456';
        await wrapper.find('input[type="text"]').setValue(stringWithSpecialCharacters);

        await wrapper.vm.$nextTick();

        const inputField: HTMLInputElement = wrapper.find('input[type="text"]').element as HTMLInputElement;
        expect(inputField.value).toEqual('ABC-123-456');
    });

    it('should add dashes to a num/char string with the length of nine after the third and sixth character', async () => {
        setWrapper();
        // A string which contains special characters will be stripped from them
        // Special characters are all characters except for integers and a-z / A-Z
        const stringWithSpecialCharacters = 'ABC123456789';
        await wrapper.find('input[type="text"]').setValue(stringWithSpecialCharacters);

        await wrapper.vm.$nextTick();

        const inputField: HTMLInputElement = wrapper.find('input[type="text"]').element as HTMLInputElement;
        expect(inputField.value).toEqual('ABC-123-456');
    });
});
