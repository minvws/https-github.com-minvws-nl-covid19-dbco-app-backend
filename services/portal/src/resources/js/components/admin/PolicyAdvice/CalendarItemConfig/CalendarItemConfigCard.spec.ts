import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import CalendarItemConfigCard from './CalendarItemConfigCard.vue';
import CalendarItemStrategyForm from './CalendarItemStrategyForm.vue';
import { fakeCalendarItemConfig, fakeCalendarItemConfigStrategy } from '@/utils/__fakes__/admin';
import { CalendarItemConfigStrategyIdentifierTypeV1, CalendarItemV1, PolicyVersionStatusV1 } from '@dbco/enum';
import type { CalendarItemConfigStrategy } from '@dbco/portal-api/admin.dto';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(CalendarItemConfigCard, {
        localVue,
        propsData: {
            ...props,
        },
    });
});

describe('CalendarItemConfigCard.vue', () => {
    it('should render 1 form for a config with itemType "point"', () => {
        const props = { config: fakeCalendarItemConfig(), versionStatus: PolicyVersionStatusV1.VALUE_draft };
        const wrapper = createComponent(props);

        expect(wrapper.findAllComponents(CalendarItemStrategyForm).length).toBe(1);
    });

    it('should render 2 forms for a config with itemType "period"', () => {
        const givenStrategy1 = fakeCalendarItemConfigStrategy({
            identifierType: CalendarItemConfigStrategyIdentifierTypeV1.VALUE_periodStart,
        });
        const givenStrategy2 = fakeCalendarItemConfigStrategy({
            identifierType: CalendarItemConfigStrategyIdentifierTypeV1.VALUE_periodEnd,
        });
        const props = {
            config: fakeCalendarItemConfig({
                itemType: CalendarItemV1.VALUE_period,
                strategies: [
                    givenStrategy1 as CalendarItemConfigStrategy,
                    givenStrategy2 as CalendarItemConfigStrategy,
                ],
            }),
            versionStatus: PolicyVersionStatusV1.VALUE_draft,
        };
        const wrapper = createComponent(props);

        const forms = wrapper.findAllComponents(CalendarItemStrategyForm);
        expect(forms.length).toBe(2);
        expect(forms.at(0).props('strategy')).toStrictEqual(givenStrategy1);
        expect(forms.at(1).props('strategy')).toStrictEqual(givenStrategy2);
    });
});
