import type {
    DateOfBirthAttributes,
    DateTimeAttributes,
    FormCondition,
    FormDropdownOptions,
    FormField,
    FormLabel,
    InputWithListProps,
} from '@/components/form/ts/formTypes';
import { validation } from '@/components/form/ts/formValidation';
import type { AllEnums, CalendarViewV1, MessageTemplateTypeV1 } from '@dbco/enum';
import { trueFalseV1Options } from '@/components/form/ts/formOptions';
import { StoreType } from '@/store/storeType';
import { userCanEdit } from '@/utils/interfaceState';
import type { Children, SchemaGenerator } from './schemaGenerator';

/**
 * This dynamic type will return the correct generator type,
 * based on a properties type
 */
export type TypedFieldGenerator<TModel extends AnyObject, T> = T extends AllEnums
    ? EnumFieldGenerator<TModel>
    : T extends AllEnums[]
    ? EnumArrayFieldGenerator<TModel>
    : T extends string
    ? StringFieldGenerator<TModel>
    : T extends string[]
    ? StringArrayFieldGenerator<TModel>
    : T extends number
    ? NumberFieldGenerator<TModel>
    : T extends Date
    ? DateFieldGenerator<TModel>
    : T extends boolean
    ? BooleanFieldGenerator<TModel>
    : T extends object[]
    ? ObjectArrayFieldGenerator<TModel>
    : T extends object
    ? ObjectFieldGenerator<TModel>
    : never;

export type EnumFieldGenerator<TModel extends AnyObject> = {
    dropdown: FieldGenerator<TModel>['dropdown'];
    groupedDropdown: FieldGenerator<TModel>['groupedDropdown'];
    placeCategory: FieldGenerator<TModel>['placeCategory'];
    radioButton: FieldGenerator<TModel>['radioButton'];
    radioCoronaMelder: FieldGenerator<TModel>['radioCoronaMelder'];
    radioButtonGroup: FieldGenerator<TModel>['radioButtonGroup'];
    relationshipDropdown: FieldGenerator<TModel>['relationshipDropdown'];
    radio: FieldGenerator<TModel>['radio'];
};

export type EnumArrayFieldGenerator<TModel extends AnyObject> = {
    checkbox: FieldGenerator<TModel>['checkbox'];
    chips: FieldGenerator<TModel>['chips'];
    multiSelectDropdown: FieldGenerator<TModel>['multiSelectDropdown'];
    radio: FieldGenerator<TModel>['radio'];
    presetOptions: FieldGenerator<TModel>['presetOptions'];
};

/**
 * Reference of all generator methods allowed for String
 */
export type StringFieldGenerator<TModel extends AnyObject> = {
    email: FieldGenerator<TModel>['email'];
    phone: FieldGenerator<TModel>['phone'];
    text: FieldGenerator<TModel>['text'];
    inputWithList: FieldGenerator<TModel>['inputWithList'];
    textArea: FieldGenerator<TModel>['textArea'];
    sendEmail: FieldGenerator<TModel>['sendEmail'];
    inputCheckbox: FieldGenerator<TModel>['inputCheckbox'];
};

export type StringArrayFieldGenerator<TModel extends AnyObject> = {
    repeatable: FieldGenerator<TModel>['repeatable'];
};

export type NumberFieldGenerator<TModel extends AnyObject> = {
    number: FieldGenerator<TModel>['number'];
    radioIntegers: FieldGenerator<TModel>['radioIntegers'];
};

export type BooleanFieldGenerator<TModel extends AnyObject> = {
    radioBoolean: FieldGenerator<TModel>['radioBoolean'];
    radioCoronaMelder: FieldGenerator<TModel>['radioCoronaMelder'];
    toggle: FieldGenerator<TModel>['toggle'];
};

export type DateFieldGenerator<TModel extends AnyObject> = {
    dateOfBirth: FieldGenerator<TModel>['dateOfBirth'];
    datePicker: FieldGenerator<TModel>['datePicker'];
    customDatePicker: FieldGenerator<TModel>['customDatePicker'];
    readonly: FieldGenerator<TModel>['readonly']; // not used on any other type
};

export type DateArrayFieldGenerator = unknown;

export type ObjectFieldGenerator<TModel extends AnyObject> = {
    addressLookup: FieldGenerator<TModel>['addressLookup'];
    addressLookupSmall: FieldGenerator<TModel>['addressLookupSmall'];
};

export type ObjectArrayFieldGenerator<TModel extends AnyObject> = {
    medicinePicker: FieldGenerator<TModel>['medicinePicker'];
    repeatableGroup: FieldGenerator<TModel>['repeatableGroup'];
    repeatableDateTime: FieldGenerator<TModel>['repeatableDateTime'];
};

// eslint-disable-next-line @typescript-eslint/ban-types
export class FieldGenerator<TModel extends AnyObject = {}> {
    private config: FormField = {
        disabled: false,
    };
    private isViewOnly = false;

    constructor(
        private generator: SchemaGenerator<TModel>,
        private name: string
    ) {
        this.isViewOnly = !userCanEdit();
        this.config.disabled = this.isViewOnly;
    }

    private address(size: 'default' | 'wide' = 'default', showRegionWarning = false, disabled = false) {
        // type Address = TModel['Address'];
        const zip: FieldGenerator = new FieldGenerator(this.generator, 'postalCode')
            .text('Postcode')
            .appendConfig({
                class: size == 'wide' ? `col-sm-6` : `col-md-6 col-lg-3 col-xl-2`,
                autocomplete: '__away', // 'off' seems not to work properly
                disabled,
            })
            .validation(['optional', 'postalCode']);
        const nr: FieldGenerator = new FieldGenerator(this.generator, 'houseNumber')
            // Housenumber should be sent to the backend as a string
            .text('Huisnummer')
            .appendConfig({
                // But we want a number input :-)
                type: 'number',
                min: '1',
                step: '1',
                class: size == 'wide' ? `col-sm-3` : `col-md-6 col-lg-3 col-xl-2`,
                autocomplete: '__away', // 'off' seems not to work properly
                '@keydown': true, // allow the FormAddressLookup component to listen
                disabled,
            })
            .validation(['optional', 'houseNumber']);

        const addition: FieldGenerator = new FieldGenerator(this.generator, 'houseNumberSuffix')
            .text('Toevoeging')
            .appendConfig({
                class: size == 'wide' ? `col-sm-3 mb-3` : `col-md-6 col-lg-3 col-xl-2 mb-3`,
                autocomplete: '__away', // 'off' seems not to work properly
                disabled,
            });

        const street: FieldGenerator = new FieldGenerator(this.generator, 'street').text('Straat').appendConfig({
            autocomplete: '__away',
            disabled: true,
            editable: false,
            loading: false,
            class: 'col-md-6',
        });
        const city: FieldGenerator = new FieldGenerator(this.generator, 'town').text('Plaats').appendConfig({
            autocomplete: '__away',
            disabled: true,
            editable: false,
            loading: false,
            class: 'col-md-6',
        });

        return {
            component: 'div',
            children: this.generator.toConfig([
                this.generator.group(
                    [
                        this.generator.group([zip, nr, addition]),
                        showRegionWarning
                            ? this.generator.div(
                                  [
                                      this.generator.icon('icon icon--error-notice flex-shrink-0 mt-1 mr-2 ml-1'),
                                      this.generator.span(
                                          'Deze postcode valt buiten je GGD-regio. Wil je deze postcode toch gebruiken? Dan kun je de context niet inzien of bewerken.',
                                          'form-font'
                                      ),
                                  ],
                                  'd-flex mt-n3 mb-3'
                              )
                            : this.generator.linebreak('d-none'),
                        this.generator.group(
                            [street.toConfig(), city.toConfig()].map((field) => {
                                if (this.isViewOnly || disabled) return field;
                                // If not isViewOnly, turn field into editable input
                                const { class: classList, ...children } = field;
                                return {
                                    type: 'formEditableInput',
                                    class: classList,
                                    data: children,
                                    children: [children],
                                };
                            })
                        ),
                    ],
                    '',
                    'w-100'
                ),
            ]),
        };
    }

    addressLookup(size: 'default' | 'wide' = 'default', showRegionWarning = false, disabled = false) {
        this.appendConfig({
            ...this.config,
            type: 'formAddressLookup',
            name: this.name,
            class: 'w100 mb-0',
            prefix: this.name,
            schema: this.address(size, showRegionWarning, disabled),
            '@change': true,
        });
        return this;
    }

    private addressSmall() {
        // type Address = TModel['Address'];
        const zip: FieldGenerator = new FieldGenerator(this.generator, 'postalCode')
            .text('Postcode')
            .appendConfig({
                class: 'col-4',
                autocomplete: '__away', // 'off' seems not to work properly
            })
            .validation(['optional', 'postalCode']);
        const nr: FieldGenerator = new FieldGenerator(this.generator, 'houseNumber')
            // Housenumber should be sent to the backend as a string
            .text('Huisnummer')
            .appendConfig({
                // But we want a number input :-)
                type: 'number',
                min: '1',
                step: '1',
                class: 'col-4',
                autocomplete: '__away', // 'off' seems not to work properly
                '@keydown': true, // allow the FormAddressLookup component to listen
            })
            .validation(['optional', 'houseNumber']);

        const addition: FieldGenerator = new FieldGenerator(this.generator, 'houseNumberSuffix')
            .text('Toevoeging')
            .appendConfig({
                class: 'col-4',
                autocomplete: '__away', // 'off' seems not to work properly
            });

        return {
            component: 'div',
            children: this.generator.toConfig([this.generator.group([zip, nr, addition])]),
        };
    }

    addressLookupSmall() {
        this.appendConfig({
            type: 'formAddressLookup',
            name: this.name,
            class: 'w100 mb-0',
            prefix: this.name,
            schema: this.addressSmall(),
            '@change': true,
        });
        return this;
    }

    appendConfig(newConfig: Partial<FormField>) {
        this.config = {
            ...this.config,
            ...newConfig,
        };
        return this;
    }

    checkbox(
        label: string,
        options: FormDropdownOptions,
        columns: 1 | 2 | 3 | 4 = 1,
        className = '',
        description = '',
        wrapperClassName = ''
    ) {
        this.select(label, options).appendConfig({
            type: 'checkbox',
            'element-class': `columns columns-${columns} ${className}`,
            class: `col-12 w100 ${wrapperClassName}`,
            description,
        });

        return this;
    }

    chips(label: string, placeholder?: string, options?: FormDropdownOptions, size = 6) {
        this.appendConfig({
            type: 'formChips',
            class: `col-${size} w100`,
            name: this.name,
            label,
            placeholder,
            '@change': true,
            options,
        });
        return this;
    }

    /**
     * Make any input conditionally readonly, make sure to define the original input first, and call this method afterwards
     * to configure it as readonly with certain conditions
     *
     * @param condition
     * @param store
     * @param readonlyProps
     * @returns
     */
    conditionalReadonly(condition: FormCondition, store: StoreType = StoreType.INDEX, readonlyProps: object = {}) {
        this.appendConfig({
            inputType: this.config.type,
            type: 'formConditionalReadonly',
            condition,
            store,
            showErrors: true,
            readonly: {
                name: this.name,
                placeholder: this.config.placeholder,
                ...readonlyProps,
            },
        });

        return this;
    }

    dateOfBirth(label: string, attributes: DateOfBirthAttributes = {}, size = 6) {
        this.appendConfig({
            type: 'formDateOfBirth',
            class: `w100 col-${size}`,
            name: this.name,
            label,
            displayAge: true,
            ...attributes,
        });

        return this;
    }

    datePicker(label: string, min?: string, max = '9999-12-31') {
        this.appendConfig({
            type: 'date',
            class: 'col-6 w100',
            name: this.name,
            label,
            min,
            max,
            '@change': true,
            // Show errors immediately on load
            showErrors: true,
            // Hides validation while typing
            showValidation: 'blur',
        });
        return this;
    }

    customDatePicker(
        label: string,
        calendarView?: CalendarViewV1,
        rangeCutOff?: Date | string,
        singleSelection?: boolean,
        disabled?: boolean,
        editable?: boolean,
        children: Children<TModel> = []
    ) {
        this.appendConfig({
            type: 'formDatePicker',
            class: 'col-6 w100',
            name: this.name,
            label,
            '@change': true,
            calendarView,
            rangeCutOff,
            singleSelection,
            disabled,
            editable,
            children: this.generator.toConfig(children),
        });

        return this;
    }

    dropdown(label: string, placeholder: string, options: FormDropdownOptions) {
        this.select(label, options);
        this.appendConfig({
            placeholder,
            type: 'select',
        });

        return this;
    }

    email(label: string, placeholder?: string) {
        this.input(label, placeholder)
            .appendConfig({
                type: 'email',
                autocomplete: 'off',
            })
            .validation('email');

        return this;
    }

    groupedDropdown(name: string, label: string, placeholder: string, options: Record<string, FormDropdownOptions>) {
        this.appendConfig({
            name,
            label,
            class: 'col-6 w100',
            'option-groups': options,
            placeholder,
            type: 'select',

            // Show error when interaction has taken place or field has value
            errorBehavior: 'value',
        });

        return this;
    }

    private input(label: string, placeholder?: string, size = 6, className = '') {
        this.appendConfig({
            name: this.name,
            label,
            placeholder,
            class: `col-${size} w100 ${className}`,
            '@blur': true,
            '@focus': true,

            // Show error when interaction has taken place or field has value
            errorBehavior: 'value',
            // Hides validation while typing
            showValidation: 'blur',
        });
        return this;
    }

    inputCheckbox(label: string, defaultText?: string, type: 'text' | 'textarea' = 'text', size = 6, className = '') {
        this.appendConfig({
            type: 'formInputCheckbox',
            class: `w100 col-${size} ${className}`,
            name: this.name,
            label,
            inputType: type,
            defaultText,
            '@change': true,
        });
        return this;
    }

    medicinePicker(label: string) {
        this.appendConfig({
            type: 'formMedicinePicker',
            class: 'col-12 w100',
            name: this.name,
            label,
            '@change': true,
        });

        return this;
    }

    multiSelectDropdown(
        label: string,
        placeholder: string,
        listOptions: FormDropdownOptions,
        listGroups?: FormDropdownOptions,
        filterEnabled?: boolean,
        size = 6
    ) {
        this.appendConfig({
            type: 'formMultiSelectDropdown',
            class: `col-${size} w100`,
            name: this.name,
            label,
            placeholder,
            '@change': true,
            listOptions,
            listGroups,
            filterEnabled,
        });

        return this;
    }

    number(label: string, placeholder?: string, className?: string) {
        this.input(label, placeholder)
            .appendConfig({
                type: 'formNumberInput',
                label,
                placeholder,
                autocomplete: 'off',
                inputmode: 'numeric',
                pattern: '[0-9]*',
                class: `form-number-input ${className}`,
                min: '0',
                max: '999',
            })
            .validation('numeric');

        return this;
    }

    phone(label: string, placeholder?: string, className = '') {
        this.text(label, placeholder);
        this.appendConfig({ class: `col-6 w100 ${className}` });

        return this;
    }

    placeCategory(label: string, size = 6) {
        this.appendConfig({
            type: 'formPlaceCategory',
            class: `w100 col-${size}`,
            label,
            name: this.name,
        });
        return this;
    }

    presetOptions(
        checkboxLabel: string,
        presetLabel: string,
        options: Record<string, string>,
        preset: Record<string, string>
    ) {
        this.appendConfig({
            type: 'formPresetOptions',
            name: this.name,
            options,
            preset,
            checkboxLabel,
            presetLabel,
            '@change': true,
            '@repeatableRemoved': 'repeatableRemoved',
        });
        return this;
    }

    radio(label: string, options: FormDropdownOptions) {
        this.select(label, options).appendConfig({
            class: 'col-6 w100 mb-0',
            type: 'radio',
        });

        return this;
    }

    radioBoolean(label: string, className?: string, elementClassName?: string) {
        return this.radioNumberBoolean(label, trueFalseV1Options, className, elementClassName);
    }

    radioButton(label: string, options: FormDropdownOptions, className = '', elementClassName = '') {
        this.radio(label, options).appendConfig({
            'wrapper-class': `radio-button-wrapper ${className}`,
            'element-class': elementClassName ? elementClassName : 'radio-button-buttons row mr-1',
            'input-class': this.config.disabled ? 'radio-button radio-button--disabled col' : 'radio-button col',
        });

        return this;
    }

    radioButtonGroup(
        label: FormLabel,
        options: FormDropdownOptions,
        expand: string[],
        children: Children<TModel> = [],
        className = '',
        childrenWrapperClass?: string
    ) {
        this.appendConfig({
            type: 'formRadioGroup',
            name: this.name,
            class: `w100 ${className}`,
            title: label,
            options,
            expand,
            childrenWrapperClass,
            children: this.generator.toConfig(children),
        });
        return this;
    }

    radioCoronaMelder(label: string, options: FormDropdownOptions) {
        this.appendConfig({
            type: 'formRadioCoronaMelder',
            class: 'w100',
            fieldAttributes: new FieldGenerator(this.generator, this.name)
                .radioNumberBoolean(label, options)
                .toConfig(),
        });
        return this;
    }

    radioIntegers(label: string, options: FormDropdownOptions) {
        this.radioNumberBoolean(label, options).appendConfig({
            format: 'number',
        });

        return this;
    }

    private radioNumberBoolean(
        label: string,
        options: FormDropdownOptions,
        className?: string,
        elementClassName?: string
    ) {
        this.appendConfig({
            type: 'formNumberBoolean',
            name: this.name,
            class: `w100 col-6 pl-0 pr-0 radio-number-boolean`,
            schema: new FieldGenerator(this.generator, this.name)
                .radioButton(label, options, className, elementClassName)
                .toConfig(),
        });

        return this;
    }

    readonly(label: string, placeholder?: string, tooltip?: string) {
        this.appendConfig({
            name: this.name,
            label,
            placeholder,
            class: 'col-6 w100',
            type: 'formReadonly',
            tooltip,
        });
        return this;
    }

    relationshipDropdown(label: string, placeholder: string) {
        this.appendConfig({
            name: this.name,
            label,
            class: 'col-6 w100',
            placeholder,
            type: 'formRelationshipDropdown',

            // Show error when interaction has taken place or field has value
            errorBehavior: 'value',
        });
        return this;
    }

    repeatable(label: string, placeholder?: string, size = 6) {
        this.appendConfig({
            type: 'formRepeatable',
            label,
            placeholder,
            name: this.name,
            class: `w100 col-${size} repeatable`,
            '@change': true,
        });
        return this;
    }

    repeatableDateTime(attributes: DateTimeAttributes = {}) {
        this.appendConfig({
            type: 'formDateTimeInputRepeated',
            class: 'w-100',
            name: this.name,
            '@change': true,
            '@repeatableRemoved': 'repeatableRemoved',
            ...attributes,
        });
        return this;
    }

    repeatableGroup(addLabel: string, children: Children<TModel>, minimum?: number, limit?: number) {
        this.appendConfig({
            type: 'formRepeatableGroup',
            name: this.name,
            autocomplete: 'off',
            addLabel,
            class: 'w-100',
            '@change': true,
            '@repeatableRemoved': 'repeatableRemoved',
            children: [
                {
                    component: 'div',
                    class: 'row',
                    children: this.generator.toConfig(children),
                },
            ],
            minimum,
            limit,
            // Pass children schema for custom purposes
            childrenSchema: this.generator.toConfig(children),
        });
        return this;
    }

    private select(label: string, options?: FormDropdownOptions, size = 6) {
        this.appendConfig({
            name: this.name,
            label,
            class: `col-${size} w100`,
            options,

            // Show error when interaction has taken place or field has value
            errorBehavior: 'value',
        });
        return this;
    }

    sendEmail(
        caseUuid: string,
        taskUuid: string | null,
        emailVariant: MessageTemplateTypeV1,
        label: string,
        variant?: string
    ) {
        this.appendConfig({
            type: 'formSendEmail',
            storeName: this.name,
            label: '',
            caseUuid,
            taskUuid,
            buttonLabel: label,
            buttonVariant: variant,
            emailVariant,
            disableErrors: true,
            class: 'mb-0',
        });
        return this;
    }

    text(label?: string, placeholder?: string, size?: number, className?: string) {
        this.input(label || '', placeholder, size, className);
        this.appendConfig({ autocomplete: 'off', type: 'text' });

        return this;
    }

    textArea(label?: string, placeholder?: string, size = 6, className = '') {
        this.input(label || '', placeholder, size, className).appendConfig({
            autocomplete: 'off',
            placeholder,
            type: 'textarea',
            '@change': true,
        });

        return this;
    }

    toConfig(): FormField {
        return this.config;
    }

    toggle(label: string) {
        this.appendConfig({
            name: this.name,
            label,
            checked: false,
            type: 'checkbox',
            class: 'col-12 w100',
            'wrapper-class': this.config.disabled
                ? 'formulate-input-wrapper formulate-input-wrapper--disabled'
                : 'formulate-input-wrapper',
        });
        return this;
    }

    validation(rules: string | string[], label?: string) {
        const validationLabel = label || this.config.label;
        if (!validationLabel) {
            throw `Adding rule(s) ${JSON.stringify(rules)} without label`;
        }

        this.appendConfig(validation(rules, validationLabel));

        return this;
    }

    inputWithList(fieldAttributes: Partial<FormField> & InputWithListProps) {
        this.appendConfig({
            name: this.name,
            type: 'formInputWithList',
            class: 'col-6 w100',
            ...fieldAttributes,
        });
        return this;
    }
}
