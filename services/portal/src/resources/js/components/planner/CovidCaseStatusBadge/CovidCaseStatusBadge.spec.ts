import { shallowMount } from '@vue/test-utils';
import CovidCaseStatusBadge from './CovidCaseStatusBadge.vue';
import { ContactTracingStatusV1, contactTracingStatusV1Options } from '@dbco/enum';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(CovidCaseStatusBadge, {
        localVue,
        propsData: props,
        attachTo: document.body,
    });
});

describe('CovidCaseStatusBadge.vue', () => {
    it.each([
        [
            ContactTracingStatusV1.VALUE_bco_finished,
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_bco_finished],
            'success',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_bco_finished],
        ],
        [
            ContactTracingStatusV1.VALUE_callback_request,
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_callback_request],
            'danger',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_callback_request],
        ],
        [
            ContactTracingStatusV1.VALUE_closed,
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_closed],
            'light-grey',
            'Deze case is gesloten',
        ],
        [
            ContactTracingStatusV1.VALUE_closed_no_collaboration,
            'Controleren',
            'success',
            'Deze case moet gecontroleerd worden',
        ],
        [
            ContactTracingStatusV1.VALUE_closed_outside_ggd,
            'Controleren',
            'success',
            'Deze case moet gecontroleerd worden',
        ],
        [ContactTracingStatusV1.VALUE_completed, 'Controleren', 'success', 'Deze case moet gecontroleerd worden'],
        [
            ContactTracingStatusV1.VALUE_four_times_not_reached,
            '4x geen gehoor',
            'warning',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_four_times_not_reached],
        ],
        [
            ContactTracingStatusV1.VALUE_loose_end,
            'Los eindje',
            'danger',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_loose_end],
        ],
        [
            ContactTracingStatusV1.VALUE_new,
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_new],
            'primary',
            'Deze case is nieuw',
        ],
        [
            ContactTracingStatusV1.VALUE_not_approached,
            'Niet benaderd',
            'danger',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_not_approached],
        ],
        [
            ContactTracingStatusV1.VALUE_not_reachable,
            'Onbereikbaar',
            'danger',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_not_reachable],
        ],
        [
            ContactTracingStatusV1.VALUE_not_started,
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_not_started],
            'primary',
            'Nieuwe case: BCO nog niet begonnen',
        ],
        [
            ContactTracingStatusV1.VALUE_conversation_started,
            'Indexgesprek',
            'warning',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_conversation_started],
        ],
        [
            ContactTracingStatusV1.VALUE_two_times_not_reached,
            '2x geen gehoor',
            'danger',
            contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_two_times_not_reached],
        ],
    ])(
        '%#: should show correct status badge for statusIndexContactTracing=%s',
        (statusIndexContactTracing, expectedTitle, expectedVariant, expectedTooltip) => {
            // GIVEN that a badge is created for a case
            const wrapper = createComponent({
                statusIndexContactTracing,
            });

            // WHEN that badge is rendered
            const badgeComponent = wrapper.findComponent({ name: 'BBadge' });

            // THEN the badge should show the expected status
            expect(badgeComponent.text()).toEqual(expectedTitle);
            expect(badgeComponent.attributes('variant')).toEqual(expectedVariant);
            expect(badgeComponent.attributes('title')).toEqual(expectedTooltip);
        }
    );
});
