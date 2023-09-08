import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, setupTest } from '@/utils/test';

import i18n from '@/i18n/index';

import CallToActionDetails from './CallToActionDetails.vue';
import { fakeCallToAction } from '@/utils/__fakes__/callToAction';
import { Role } from '@dbco/portal-api/user';
import { formatDate, parseDate } from '@/utils/date';

const createComponent = setupTest((localVue: VueConstructor, givenProps?: object) => {
    return shallowMount<CallToActionDetails>(CallToActionDetails, {
        localVue,
        i18n,
        propsData: givenProps,
    });
});

describe('CallToActionDetails.vue', () => {
    it('should render the subject of a given call to action as 4th level heading', () => {
        // GIVEN a call to action
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN the component should have a fourth level heading with the subject
        expect(wrapper.find('h4').text()).toBe(fakeCallToAction.subject);
    });

    it('should render a translated title for the description', () => {
        // GIVEN a call to action
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should render a translated title for: description
        const translatedTitles = wrapper.findAll('h5');
        expect(translatedTitles.at(0).text()).toBe(i18n.t('components.callToActionSidebar.titles.description'));
    });

    it('should not render a description when the call to action is not picked up', () => {
        // GIVEN a call to action that is not picked up
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should not render a description
        const texts = wrapper.findAll('p');
        expect(texts.at(0).text()).toBe('-');
    });

    it('should render a description when the call to action is picked up', () => {
        // GIVEN a call to action with a description
        const fakeCTAWithDescription = { ...fakeCallToAction, ...{ description: fakerjs.lorem.sentence() } };

        // WHEN the component renders with it picked up
        const wrapper = createComponent({
            callToAction: fakeCTAWithDescription,
            pickedUp: true,
        });

        // THEN it should render a description
        const texts = wrapper.findAll('p');
        expect(texts.at(0).text()).toBe(fakeCTAWithDescription.description);
    });

    it('should render a translated title for the deadline', () => {
        // GIVEN a call to action
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should render a translated title for: deadline
        const translatedTitles = wrapper.findAll('h5');
        expect(translatedTitles.at(1).text()).toBe(i18n.t('components.callToActionSidebar.titles.deadline'));
    });

    it('should render a formatted expiration date', () => {
        // GIVEN a call to action
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should render a formatted expiration date
        const texts = wrapper.findAll('p');
        expect(texts.at(1).text()).toBe(formatDate(parseDate(fakeCallToAction.expiresAt), 'dd MMMM yyyy'));
    });

    it('should render a translated title for the createdBy info', () => {
        // GIVEN a call to action
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should render a translated title for: createdby
        const translatedTitles = wrapper.findAll('h5');
        expect(translatedTitles.at(2).text()).toBe(i18n.t('components.callToActionSidebar.titles.created_by'));
    });

    it('should not render createdBy info when the call to action is not picked up', () => {
        // GIVEN a call to action that is not picked up
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should not render createdBy info
        const texts = wrapper.findAll('p');
        expect(texts.at(2).text()).toBe('-');
    });

    it('should render createdBy info when the call to action is picked up', () => {
        // GIVEN a call to action with createdBy info
        const fakeCTAWithCreatedByInfo = {
            ...fakeCallToAction,
            ...{
                createdBy: {
                    name: fakerjs.person.fullName(),
                    roles: [Role.user],
                    uuid: fakerjs.string.uuid(),
                },
            },
        };

        // WHEN the component renders with it picked up
        const wrapper = createComponent({
            callToAction: fakeCTAWithCreatedByInfo,
            pickedUp: true,
        });

        // THEN it should render the createdBy info
        const texts = wrapper.findAll('p');
        expect(texts.at(2).text()).toBe(`${fakeCTAWithCreatedByInfo.createdBy.name}, ${i18n.t(`roles.${Role.user}`)}`);
    });

    it('should render a translated hint when a call to action without createdBy info is picked up', () => {
        // GIVEN a call to action without createdBy info
        // WHEN the component renders with it picked up
        const wrapper = createComponent({
            callToAction: fakeCallToAction,
            pickedUp: true,
        });

        // THEN it should render a translated hint
        const texts = wrapper.findAll('p');
        expect(texts.at(2).text()).toBe(i18n.t('components.callToActionSidebar.hints.no_created_by_info'));
    });

    it('should render a formatted creation date as small print', () => {
        // GIVEN a call to action with a creation date
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should render the creation date as small print
        const smallPrint = wrapper.find('small');
        expect(smallPrint.text()).toBe(formatDate(parseDate(fakeCallToAction.createdAt), 'd MMMM yyyy HH:mm'));
    });

    it('should render a translated hint when there is no creation date', () => {
        // GIVEN a call to action without a creation date
        const fakeCTAWithoutCreationDate = {
            ...fakeCallToAction,
            ...{
                createdAt: undefined,
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCTAWithoutCreationDate, pickedUp: false });

        // THEN it should render a translated hint
        const smallPrint = wrapper.find('small');
        expect(smallPrint.text()).toBe(i18n.t('components.callToActionSidebar.hints.no_creation_date'));
    });
});
