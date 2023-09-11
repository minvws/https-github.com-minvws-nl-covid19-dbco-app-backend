import type { VueConstructor } from 'vue';
import { mount } from '@vue/test-utils';
import { fakerjs, setupTest } from '@/utils/test';
import RiskProfileTooltip from './RiskProfileTooltip.vue';
import { TooltipButton } from '@dbco/ui-library';

vi.mock('@/router/router');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(RiskProfileTooltip, {
        localVue,
        propsData: {
            ...props,
        },
    });
});

describe('RiskProfileTooltip.vue', () => {
    it.each([['hospital_admitted'], ['has_symptoms'], ['is_immuno_compromised'], ['no_symptoms']])(
        'should render a tooltip for %s',
        (inputType) => {
            const wrapper = createComponent({
                uuid: fakerjs.string.uuid(),
                name: 'Verminderde afweer',
                policyVersionUuid: fakerjs.string.uuid(),
                policyGuidelineUuid: fakerjs.string.uuid(),
                sortOrder: fakerjs.number.int(),
                isActive: fakerjs.datatype.boolean(),
                riskProfileEnum: inputType,
            });
            const tooltip = wrapper.findComponent(TooltipButton);

            expect(wrapper.find(`[data-testid="${inputType}"]`).exists()).toBe(true);
            expect(tooltip.html()).toContain(inputType);
        }
    );
});
