import type {
    FormCondition,
    FormConditionOperator,
    FormField,
    FormLabel,
    InfoProps,
} from '@/components/form/ts/formTypes';
import { StoreType } from '@/store/storeType';
import { userCanEdit } from '@/utils/interfaceState';
import type { SafeHtml } from '@/utils/safeHtml';
import { ElementGenerator } from './elementGenerator';
import type { TypedFieldGenerator } from './fieldGenerator';
import { FieldGenerator } from './fieldGenerator';

/**
 * Children can be instances of FieldGenerator, of plain objects compliant to FormField
 */
export type Children<TModel extends AnyObject> = (FieldGenerator<TModel> | ElementGenerator | FormField)[];

/**
 * Initiates a generator and allows to pass the model for the fragments as a generic
 */
export class SchemaGenerator<TModel extends AnyObject> {
    private isViewOnly = false;

    constructor() {
        this.isViewOnly = !userCanEdit();
    }

    button(
        label: string,
        type: 'button' | 'submit' = 'button',
        size = 12,
        className = '',
        style: 'block' | 'inline' = 'block'
    ) {
        return new ElementGenerator({
            type,
            label,
            class: `w100 col-${size}`,
            'input-class': `button-${style} ${className}`,
            disabled: this.isViewOnly,
        });
    }

    buttonModal(label: string, modal: string, size = 12) {
        return this.button(label, 'button', size, 'button-link').appendConfig({
            '@click': () => {
                window.app.$root.$emit('bv::toggle::modal', modal);
            },
        });
    }

    buttonLink(label: string, url: string, size = 12) {
        return this.button(label, 'button', size, 'button-link').appendConfig({
            '@click': () => {
                const win = window.open(url, undefined, 'noopener,noreferrer');
                win && win.focus();
            },
            disabled: false, // external links so no need to disable
        });
    }

    /**
     * Renders an expand button if none of the fields in children have a value
     *
     * @param title
     * @param buttonText
     * @param children
     * @param labelComponent
     * @param className
     * @returns
     */
    buttonToggleGroup(
        title: FormLabel,
        buttonText: FormLabel,
        children: Children<TModel> = [],
        // eslint-disable-next-line @typescript-eslint/ban-types
        labelComponent?: Function,
        // eslint-disable-next-line @typescript-eslint/ban-types
        buttonComponent?: Function,
        className = ''
    ) {
        return {
            buttonText,
            children: this.toConfig(children),
            // Pass children schema for custom purposes
            childrenSchema: this.toConfig(children),
            class: `col-12 px-0 ${className}`,
            title,
            type: 'formButtonToggleGroup',
            labelComponent,
            buttonComponent,
            disabled: this.isViewOnly,
        };
    }

    // eslint-disable-next-line @typescript-eslint/ban-types
    component(component: Function, props: any = {}, children: Children<TModel> = []) {
        return new ElementGenerator({
            component,
            ...props,
            class: `${props.class || ''}`,
            children: this.toConfig(children),
        });
    }

    contextCategorySuggestions(className = 'col-12 m-0') {
        return new ElementGenerator({
            type: 'formContextCategorySuggestions',
            autocomplete: 'off',
            class: className,
        });
    }

    dateDifferenceLabel(dateName: string, baseDateName?: string, baseDateLabel?: string) {
        return new ElementGenerator({
            type: 'formDateDifferenceLabel',
            class: `px-3`,
            dateName,
            baseDateName,
            baseDateLabel,
        });
    }

    div(children: Children<TModel>, className = '') {
        return new ElementGenerator({
            component: 'div',
            class: `${className}`,
            children: this.toConfig(children),
        });
    }
    field<K extends keyof TModel, TGenerator extends TypedFieldGenerator<TModel, TModel[K]>>(property: K): TGenerator;
    field<
        K extends keyof TModel,
        FK extends keyof TModel[K],
        TGenerator extends TypedFieldGenerator<TModel, TModel[K][FK]>,
    >(property: K, fragmentProperty?: FK): TGenerator;
    field<
        K extends keyof TModel,
        FK extends keyof TModel[K],
        TGenerator extends TypedFieldGenerator<TModel, TModel[K][FK]>,
    >(property: K, fragmentProperty?: FK): TGenerator {
        return new FieldGenerator<TModel>(
            this,
            `${String(property)}${fragmentProperty ? `.${String(fragmentProperty)}` : ''}`
        ) as unknown as TGenerator;
    }

    formChapter(children: Children<TModel>, title?: string, isCard = true, className = '') {
        return new ElementGenerator({
            component: 'div',
            children: [
                ...(title ? [this.heading(title).toConfig()] : []),
                {
                    component: 'div',
                    class: `form-chapter${isCard ? '' : '--not-card'} ${className}`,
                    children: this.toConfig(children),
                },
            ],
            '@change': true,
            '@repeatableRemoved': 'repeatableRemoved',
        });
    }

    group(children: Children<TModel>, className = '', childrenClassName = '') {
        return new ElementGenerator({
            component: 'div',
            class: `${className}`,
            children: [
                {
                    component: 'div',
                    class: childrenClassName ? childrenClassName : 'row',
                    children: this.toConfig(children),
                },
            ],
        });
    }

    heading(title: string, component = 'h3', className = '') {
        return new ElementGenerator({
            component,
            children: title,
            class: `form-heading ${className}`,
        });
    }

    icon(className = '') {
        return new ElementGenerator({
            component: 'i',
            class: `${className}`,
        });
    }

    info(
        text: string | SafeHtml,
        showIcon = true,
        size = 12,
        infoType: InfoProps['infoType'] = 'info',
        className = ''
    ) {
        return new ElementGenerator({
            type: 'formInfo',
            class: `col-${size} ${className}`,
            text,
            infoType,
            showIcon,
        });
    }

    feedback(
        title: string,
        conditions: FormCondition[],
        store: StoreType = StoreType.INDEX,
        className = '',
        conditionsMetIcon?: string
    ) {
        return new ElementGenerator({
            type: 'formFeedback',
            class: `d-flex mb-2 ${className}`,
            title,
            conditions,
            store,
            conditionsMetIcon,
        });
    }

    label(label: string, className = 'col mb-2') {
        return new ElementGenerator({
            type: 'label',
            class: className,
            label,
        });
    }

    li(content: string, className = '') {
        return new ElementGenerator({
            component: 'li',
            children: content,
            class: `${className}`,
        });
    }

    linebreak(className = '') {
        return new ElementGenerator({
            component: 'div',
            class: `${className} line-break mb-4`,
        });
    }

    static orphanField(fieldName: string) {
        return new FieldGenerator(new SchemaGenerator(), fieldName);
    }

    paragraph(content: string, className = '') {
        return new ElementGenerator({
            component: 'p',
            children: content,
            class: `d-flex mb-2 ${className}`,
        });
    }

    printDate(name: string, className = '', stringBefore = '', stringAfter = '') {
        return new ElementGenerator({
            type: 'form-print-date',
            class: `mb-0 ${className}`,
            name,
            stringBefore,
            stringAfter,
        });
    }

    printValue(name: string, className = '', stringBefore = '', stringAfter = '') {
        return new ElementGenerator({
            type: 'form-print-value',
            class: `mb-0 ${className}`,
            name,
            stringBefore,
            stringAfter,
        });
    }

    span(text: string, className = '') {
        return new ElementGenerator({
            component: 'span',
            class: `${className}`,
            children: text,
        });
    }

    slot(
        children: Children<TModel>,
        conditions: FormCondition[],
        store: StoreType = StoreType.INDEX,
        size = 12,
        className = '',
        conditionOperator: FormConditionOperator = 'AND'
    ) {
        return new ElementGenerator({
            type: 'formSlot',
            class: `col-${size} px-0 w100 ${className}`,
            store,
            conditions,
            children: this.toConfig(children),
            conditionOperator,
        });
    }

    toConfig(items: Children<TModel>): FormField[] {
        return items.map((item) => {
            if (item instanceof FieldGenerator || item instanceof ElementGenerator) {
                return item.toConfig();
            }

            return item;
        });
    }

    ul(children: Children<TModel>, className = '') {
        return new ElementGenerator({
            component: 'ul',
            children: this.toConfig(children),
            class: `container ${className}`,
        });
    }
}
