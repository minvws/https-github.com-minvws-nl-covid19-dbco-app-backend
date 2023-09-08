import type { ComponentOptions, defineComponent } from 'vue';
import Vue from 'vue';
import VueFormulate from '@braid/vue-formulate';
import { nl } from '@braid/vue-formulate-i18n';
import { lcFirst } from './utils/string';

import FormulateFormWrapper from './components/form/FormulateFormWrapper/FormulateFormWrapper.vue';
import FormLabel from './components/form/FormLabel/FormLabel.vue';
import FormErrors from './components/form/FormErrors/FormErrors.vue';
import FormRenderer from './components/form/FormRenderer/FormRenderer.vue';
import FormButtonContent from './components/form/FormButtonContent/FormButtonContent.vue';

import FormAddressLookup from './components/form/FormAddressLookup/FormAddressLookup.vue';
import FormButtonToggleGroup from './components/form/FormButtonToggleGroup/FormButtonToggleGroup.vue';
import FormChips from './components/form/FormChips/FormChips.vue';
import FormConditionalReadonly from './components/form/FormConditionalReadonly/FormConditionalReadonly.vue';
import FormContextCategorySuggestions from './components/form/FormContextCategorySuggestions/FormContextCategorySuggestions.vue';
import FormDateDifferenceLabel from './components/form/FormDateDifferenceLabel/FormDateDifferenceLabel.vue';
import FormDateOfBirth from './components/form/FormDateOfBirth/FormDateOfBirth.vue';
import FormDateTimeInputRepeated from './components/form/FormDateTimeInputRepeated/FormDateTimeInputRepeated.vue';
import FormEditableInput from './components/form/FormEditableInput/FormEditableInput.vue';
import FormFeedback from './components/form/FormFeedback/FormFeedback.vue';
import FormInfo from './components/form/FormInfo/FormInfo.vue';
import FormInputCheckbox from './components/form/FormInputCheckbox/FormInputCheckbox.vue';
import FormMedicinePicker from './components/form/FormMedicinePicker/FormMedicinePicker.vue';
import FormDatePicker from './components/form/FormDatePicker/FormDatePicker.vue';
import FormMultiSelectDropdown from './components/form/FormMultiSelectDropdown/FormMultiSelectDropdown.vue';
import FormNumberBoolean from './components/form/FormNumberBoolean/FormNumberBoolean.vue';
import FormNumberInput from './components/form/FormNumberInput/FormNumberInput.vue';
import FormPlaceCategory from './components/form/FormPlaceCategory/FormPlaceCategory.vue';
import FormInputWithList from './components/form/FormInputWithList/FormInputWithList.vue';
import FormPresetOptions from './components/form/FormPresetOptions/FormPresetOptions.vue';
import FormRadioCoronaMelder from './components/form/FormRadioCoronaMelder/FormRadioCoronaMelder.vue';
import FormRadioGroup from './components/form/FormRadioGroup/FormRadioGroup.vue';
import FormReadonly from './components/form/FormReadonly/FormReadonly.vue';
import FormRelationshipDropdown from './components/form/FormRelationshipDropdown/FormRelationshipDropdown.vue';
import FormRepeatable from './components/form/FormRepeatable/FormRepeatable.vue';
import FormRepeatableGroup from './components/form/FormRepeatableGroup/FormRepeatableGroup.vue';
import FormSendEmail from './components/form/FormSendEmail/FormSendEmail.vue';
import FormSlot from './components/form/FormSlot/FormSlot.vue';
import { formatDate, parseDate } from './utils/date';

/**
 * Array of the custom VueFormulate components, and their classification
 */
const componentDeclarations: ComponentDeclaration[] = [
    [FormAddressLookup, 'text'],
    [FormButtonToggleGroup, 'text'],
    [FormChips, 'text'],
    [FormConditionalReadonly, 'text'],
    [FormContextCategorySuggestions, 'text'],
    [FormDateDifferenceLabel, 'text'],
    [FormDateOfBirth, 'text'],
    [FormDateTimeInputRepeated, 'dateTime'],
    [FormEditableInput, 'text'],
    [FormFeedback, 'text'],
    [FormInfo, 'text'],
    [FormInputCheckbox, 'text'],
    [FormMedicinePicker, 'text'],
    [FormDatePicker, 'text'],
    [FormMultiSelectDropdown, 'text'],
    [FormNumberBoolean, 'number'],
    [FormNumberInput, 'number'],
    [FormPlaceCategory, 'text'],
    [FormPresetOptions, 'text'],
    [FormRadioCoronaMelder, 'text'],
    [FormRadioGroup, 'text'],
    [FormReadonly, 'text'],
    [FormRelationshipDropdown, 'text'],
    [FormRepeatable, 'text'],
    [FormRepeatableGroup, 'text'],
    [FormSendEmail, 'text'],
    [FormSlot, 'text'],
    [FormInputWithList, 'text'],
];

/**
 * Extended the returntype a bit to expose the options property in the Types
 */
type TsComponent = ReturnType<typeof defineComponent> & { options?: ComponentOptions<Vue> };

/**
 * Config array items signature
 */
type ComponentDeclaration = [TsComponent | AnyObject, string];

/**
 * Typeguard for checking if the component is a Ts defined component from 'defineComponent'
 * @param component
 * @returns
 */
const isTsComponent = (component: any): component is TsComponent => typeof component.options === 'object'; // eslint-disable-line @typescript-eslint/no-explicit-any

/**
 * Custom VueFormulate components config
 */
const library: AnyObject = {};

/**
 * Registers the components to the library object
 *
 * @param [component, classification] array with respectively the component, and the classification config string
 */
export const registerComponent = (component: TsComponent | AnyObject, classification: string) => {
    const options = isTsComponent(component) && component.options ? component.options : component;

    if (!options || !options.name)
        throw { message: 'Could not determine component name for registering with Vue Formulate', component };

    const { name, props = {} } = options;

    library[lcFirst(name)] = {
        classification,
        component,
        slotProps: {
            component: Object.keys(props).filter((key) => key !== 'context'),
        },
    };
};

// Register them all
componentDeclarations.forEach(([component, classification]) => registerComponent(component, classification));

// VueFormulate wrapper and renderer
Vue.component('FormulateFormWrapper', FormulateFormWrapper);
Vue.component('FormRenderer', FormRenderer);

// Register VueFormulate
Vue.use(VueFormulate, {
    plugins: [nl],
    locale: 'nl',
    locales: {
        nl: {
            before({ args, name }: { args: string; name: string }) {
                return `${name} moet voor ${formatDate(parseDate(args, 'yyyy-MM-dd'), 'dd-MM-yyyy')} zijn.`;
            },
            after({ args, name }: { args: string; name: string }) {
                return `${name} moet na ${formatDate(parseDate(args, 'yyyy-MM-dd'), 'dd-MM-yyyy')} zijn.`;
            },
        },
    },
    validationNameStrategy: ['validationName', 'label', 'name', 'type'],
    slotComponents: {
        label: FormLabel,
        buttonContent: FormButtonContent,
        errors: FormErrors,
    },
    slotProps: {
        label: ['description'],
    },
    library,
});
