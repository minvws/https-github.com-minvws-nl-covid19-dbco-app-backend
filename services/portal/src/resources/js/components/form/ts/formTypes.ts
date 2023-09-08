/* istanbul ignore file */
import type { BcoPhaseV1, CalendarViewV1, MessageTemplateTypeV1 } from '@dbco/enum';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import type { SafeHtml } from '@/utils/safeHtml';
import type { Ref } from 'vue';

export enum BsnLookupType {
    Index = 'index',
    Task = 'task',
}

export enum CaseFilterKey {
    Source = 'source',
    TestDate = 'test-date',
    QuarantineEnd = 'quarantine-end',
    InfectiousPeriod = 'infectious',
    SymptomOnset = 'symptomonset',
    EpisodePeriod = 'episode',
}

export enum ContactFilterKey {
    Source = 'source',
    ContactDate = 'contact-dates',
    TestDate = 'test-date',
    InfectiousPeriod = 'infectious',
    QuarantinePeriod = 'quarantine',
    QuarantineEnd = 'quarantine-end',
    EpisodePeriod = 'episode',
}

export enum ContextGroup {
    Contagious = 'contagious',
    Source = 'source',
    All = 'all',
}

type FieldValidation = {
    pattern?: string;
    validation?: string | any[];
    'validation-messages'?: {
        required?: string;
        matches?: string;
        before?: ({ args }: any) => string;
        after?: ({ args }: any) => string;
    };
};

type FieldClasses = {
    'outer-class'?: string[] | string;
    'wrapper-class'?: string[] | string;
    'label-class'?: string[] | string;
    'element-class'?: string[] | string;
    'input-class'?: string[] | string;
    'errors-class'?: string[] | string;
    'error-class'?: string[] | string;
};

type FormSchemaVersion = {
    schemaVersion?: number;
};

type FormEvents = {
    '@blur'?: boolean;
    '@focus'?: boolean;
    '@change'?: boolean;
    '@keydown'?: boolean;
    '@click'?: string | (() => void);
};

type ErrorProps = {
    errorBehavior?: string;
    showValidation?: string;
    showErrors?: boolean;
    disableErrors?: boolean;
};

type VueFormulateProps = {
    component?: string;
};

export type InfoProps = {
    infoType: 'success' | 'info' | 'warning';
    showIcon: boolean;
    text?: string | SafeHtml;
};

type ConditionalReadonlyProps = {
    input?: FormField;
    inputType: FormField['type'];
    condition: FormCondition;
    conditionOperator: FormConditionOperator;
    store?: string;
    readonly: FormField;
};

type GroupProps = {
    conditions: FormCondition[];
};

type SlotProps = {
    store: string;
    conditions: FormCondition[];
};

type ReadonlyProps = {
    tooltip?: string;
};

type SendEmailProps = {
    caseUuid: string;
    taskUuid: string | null;
    buttonLabel: string;
    buttonVariant: string;
    emailVariant: MessageTemplateTypeV1;
    storeName: string;
};

type AddressFieldProps = {
    editable: boolean;
    loading: boolean;
};

type AddressLookupProps = {
    prefix: string;
    schema: FormField;
};

type GroupedDropdownProps = {
    'option-groups'?: Record<string, FormDropdownOptions>;
};

type MultiSelectDropdownProps = {
    listOptions: any;
    listGroups?: any;
    filterEnabled?: boolean;
};

type RepeatableProps = {
    repeatable: boolean;
    addLabel: string;
    '@repeatableRemoved'?: string;
};

type RepeatableGroupProps = {
    childrenSchema: FormField[];
};

type RadioIntegersProps = {
    format: string;
};

type CheckboxProps = {
    description?: string;
};

type PresetOptionsProps = {
    preset: FormDropdownOptions;
    checkboxLabel: string;
    presetLabel: string;
};

type InputCheckboxProps = {
    defaultText?: string;
};

type ButtonLinkProps = {
    id: string;
};

type DateDifferenceLabelProps = {
    dateName: string;
    baseDateName: string;
    baseDateLabel: string;
};

type RadioCoronaMelderProps = {
    fieldAttributes: FormField;
};

type PrintElementProps = {
    stringBefore: string;
    stringAfter: string;
};

export type InputWithListProps = {
    list: string[];
};

export type FormField = {
    type?: string;
    name?: string;
    placeholder?: string;
    value?: any; //null | boolean | number | string | string[];
    label?: string;
    title?: FormLabel;
    disabled?: boolean;
    checked?: boolean;
    inputmode?: string;
    class?: string;
    children?: string | FormField[];
    options?: FormDropdownOptions;
    expand?: string[];
    min?: string;
    max?: string;
    minimum?: number;
    limit?: number;
    maxlength?: number;
    step?: string;
    autocomplete?: string;
    childrenWrapperClass?: string;
    debounce?: number;
    style?: string;
    ranges?: CalendarDateRange[];
    rangeCutOff?: Date | string;
    singleSelection?: boolean;
    conditionsMetIcon?: string;
} & ErrorProps &
    FieldClasses &
    FieldValidation &
    FormEvents &
    FormSchemaVersion &
    Partial<AddressFieldProps> &
    Partial<AddressLookupProps> &
    Partial<ButtonLinkProps> &
    Partial<ConditionalReadonlyProps> &
    Partial<CheckboxProps> &
    Partial<DateDifferenceLabelProps> &
    Partial<DateTimeAttributes> &
    Partial<DateOfBirthAttributes> &
    Partial<GroupProps> &
    Partial<GroupedDropdownProps> &
    Partial<InfoProps> &
    Partial<InputCheckboxProps> &
    Partial<MultiSelectDropdownProps> &
    Partial<PresetOptionsProps> &
    Partial<PrintElementProps> &
    Partial<RadioCoronaMelderProps> &
    Partial<RadioIntegersProps> &
    Partial<ReadonlyProps> &
    Partial<RepeatableGroupProps> &
    Partial<RepeatableProps> &
    Partial<SendEmailProps> &
    Partial<SlotProps> &
    Partial<VueFormulateProps> &
    Partial<InputWithListProps>;

export type FormLabel = string | (() => string);

export type FormDropdownOptions = Record<string, string> | { value: string; label: string }[];

export type Address = {
    postalCode: string;
    houseNumber: string;
    houseNumberSuffix?: string;
    street: string;
    town: string;
    country: string;
};

export type Organisation = {
    uuid: string;
    abbreviation: string;
    bcoPhase: BcoPhaseV1;
    externalId: string;
    hpZoneCode: string | null;
    name: string;
    phoneNumber: string | null;
};
export type Moment = {
    day: string;
    startTime: string;
    endTime: string;
};

export type AssignmentConflict = {
    caseId: string;
    assignmentStatus: string;
};

export type DateTimeAttributes = {
    /**
     * Display ranges of these types in the datepicker (options: source, infectious, symptomonset, test, quarantine-end)
     */
    calendarView?: CalendarViewV1;
    rangeCutOff?: string | Date;
};

export type DateOfBirthAttributes = {
    /**
     * Display the age below the input, default: true
     */
    displayAge?: boolean;
};

export enum FormConditionRule {
    DateInSourcePeriod = 'DATE_IN_SOURCE_PERIOD',
    MedicalPeriodInfoIncomplete = 'MEDICAL_PERIOD_INFO_INCOMPLETE',
    MedicalPeriodInfoNotDefinitive = 'MEDICAL_PERIOD_INFO_NOT_DEFINITIVE',
    PotentiallyVaccinated = 'POTENTIALLY_VACCINATED',
    PreviouslyInfectedAndPotentiallyVaccinated = 'PREVIOUSLY_INFECTED_AND_POTENTIALLY_VACCINATED',
    RecentlyInfected = 'RECENTLY_INFECTED',
    HasValues = 'HAS_VALUES',
    HasValuesOrExtensiveBCO = 'HAS_VALUES_OR_EXTENSIVE_BCO',
}

export type FormRuleCondition = {
    /**
     * A rule to apply instead the default comparison
     */
    rule: FormConditionRule;

    /**
     * The property name to check
     */
    prop?: string;

    /**
     * If true, invert the result of the condition
     */
    not?: boolean;

    /**
     * The getter in the store
     */
    getter?: string;
};

export type FormPropCondition = {
    /**
     * The property name to check
     */
    prop: string;
    /**
     * The values that should be matched
     */
    values: (string | number | boolean | null | undefined)[];
    /**
     * If true, invert the result of the condition
     */
    not?: boolean;
    /**
     * The getter in the store
     */
    getter?: string;
};

export type FormFieldCondition = {
    /**
     * The Form field to check
     */
    field: string;
    /**
     * The values that should be matched
     */
    values: (string | number | boolean | null | undefined)[];
    /**
     * If true, invert the result of the condition
     */
    not?: boolean;
};

export type FormCondition = FormRuleCondition | FormPropCondition | FormFieldCondition | Ref<boolean>;

export type FormConditionOperator = 'AND' | 'OR';

/**
 * Typeguard for checking if a FormCondition is a FormRuleCondition
 */
export const isRuleCondition = (condition: FormCondition): condition is FormRuleCondition => {
    return condition.hasOwnProperty('rule');
};
export const isPropCondition = (condition: FormCondition): condition is FormPropCondition => {
    return condition.hasOwnProperty('values');
};
export const isFieldCondition = (condition: FormCondition): condition is FormFieldCondition => {
    return condition.hasOwnProperty('field');
};

export type VueFormulateContext = {
    model: any;
    options: FormDropdownOptions;
    attributes: {
        placeholder: string;
        displayAge: boolean;
    };
    addLabel: string;
    id: string;
    minimum: number;
    limit: number;
    type: string;
    name: string;
    classes: {
        groupRepeatableRemove: string;
    };
    removeLabel: string;
    isSubField: () => boolean;
};

export interface VueFormulateValidationEvent {
    name: string;
    errors: string[];
    hasErrors: boolean;
}

/**
 * The form errors object currently used by `resources/js/components/form/FormErrors/FormErrors.vue`
 */
export type FormInputErrors = {
    warning?: string[];
    fatal?: string[];
    notice?: string[];
};

export type FormErrors<T extends AnyObject> = {
    [K in keyof T]?: string; //  JSON string containing `FormInputErrors`
};
