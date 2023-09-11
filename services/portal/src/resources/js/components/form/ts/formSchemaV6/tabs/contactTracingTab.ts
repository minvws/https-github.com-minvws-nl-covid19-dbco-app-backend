import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { useContextTableStore } from '@/store/context/contextTableStore';
import { useTaskTableStore } from '@/store/task/taskTableStore/taskTableStore';
import { BcoTypeV1 } from '@dbco/enum';
import { Icon } from '@dbco/ui-library';
import { computed } from 'vue';
import type { AllowedVersions } from '..';
import { isNo, isUnknown, isYes } from '../../formOptions';
import * as contactTracingTabV1 from '../../formSchemaV1/tabs/contactTracingTab';
import * as contactTracingTabV2 from '../../formSchemaV2/tabs/contactTracingTab';
import * as contactTracingTabV4 from '../../formSchemaV4/tabs/contactTracingTab';
import { FormConditionRule } from '../../formTypes';
import { standardBCOInfoSchema } from '../shared/bcoType';
import { StoreType } from '@/store/storeType';
import store from '@/store';

export const contactsFormInfoSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [contactTracingTabV1.formInfoSchema(generator)],
            [
                computed(() => {
                    /** Show only when: contact tracing is standard AND the contact tab is NOT empty
                     * The empty tab check is an OR combination of conditions
                     * This AND/OR combination cannot be achieved in separate FormConditionRules
                     */
                    const conditions: Array<boolean> = [];
                    conditions.push(
                        store.getters[`${StoreType.INDEX}/fragments`].extensiveContactTracing
                            ?.receivesExtensiveContactTracing === BcoTypeV1.VALUE_standard
                    );
                    conditions.push(useTaskTableStore().taskCounts.contact === 0);
                    conditions.push(useContextTableStore().contagiousContextCount === 0);
                    conditions.push(
                        ![isNo, isUnknown, isYes].includes(
                            store.getters[`${StoreType.INDEX}/fragments`].contacts?.estimatedMissingContacts
                        )
                    );
                    conditions.push(
                        ![isNo, isUnknown, isYes].includes(
                            store.getters[`${StoreType.INDEX}/fragments`].groupTransport?.withReservedSeats
                        )
                    );
                    const contactsTabIsEmpty = conditions.every((condition) => condition === true);
                    return !contactsTabIsEmpty;
                }),
            ]
        )
        .appendConfig({ 'outer-class': '' });

export const contactsSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [
                generator.formChapter(
                    contactTracingTabV2.contactsSchema(generator),
                    'Contacten binnen besmettelijke periode'
                ),
            ],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                    not: true,
                },
                computed(() => useTaskTableStore().taskCounts.contact !== 0),
            ],
            undefined,
            undefined,
            undefined,
            'OR'
        )
        .appendConfig({ 'outer-class': '' });

export const contactsCountSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [generator.formChapter(contactTracingTabV4.contactsCountSchema(generator))],
            [
                {
                    rule: FormConditionRule.HasValuesOrExtensiveBCO,
                    prop: 'contacts.estimatedMissingContacts',
                },
            ]
        )
        .appendConfig({ 'outer-class': '' });

export const contactsContagiousPeriodSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [
                generator.formChapter(
                    contactTracingTabV1.contactsContagiousPeriodSchema(generator),
                    'Contexten binnen besmettelijke periode'
                ),
            ],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                    not: true,
                },
                computed(() => useContextTableStore().contagiousContextCount !== 0),
            ],
            undefined,
            undefined,
            undefined,
            'OR'
        )
        .appendConfig({ 'outer-class': '' });

export const groupTransportSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [
                generator.formChapter(
                    contactTracingTabV1.groupTransportSchema(generator),
                    'Vliegreis of groepsvervoer met gereserveerde stoelen'
                ),
            ],
            [
                {
                    rule: FormConditionRule.HasValuesOrExtensiveBCO,
                    prop: 'groupTransport.withReservedSeats',
                },
            ]
        )
        .appendConfig({ 'outer-class': '' });

export const contactsBCOInfoSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [standardBCOInfoSchema(generator)],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                },
                computed(() => {
                    const conditions: Array<boolean> = [];
                    conditions.push(useTaskTableStore().taskCounts.contact !== 0);
                    conditions.push(useContextTableStore().contagiousContextCount !== 0);
                    conditions.push(
                        [isNo, isUnknown, isYes].includes(
                            store.getters[`${StoreType.INDEX}/fragments`].contacts?.estimatedMissingContacts
                        )
                    );
                    conditions.push(
                        [isNo, isUnknown, isYes].includes(
                            store.getters[`${StoreType.INDEX}/fragments`].groupTransport?.withReservedSeats
                        )
                    );
                    return conditions.some((condition) => condition === true);
                }),
            ]
        )
        .appendConfig({ 'outer-class': '' });

export const contactsInfoSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [
                generator.div(
                    [
                        generator.component(Icon, {
                            name: 'exclamation-mark-triangle',
                            class: 'tw-mr-2 tw-text-violet-700 tw-w-5 tw-h-5',
                        }),
                        generator.span(
                            'De vragen in dit tabblad komen niet terug in een standaard BCO. Kies voor een uitgebreid BCO om deze vragen te tonen.',
                            'tw-font-sans'
                        ),
                    ],
                    'tw-flex tw-items-center tw-py-6 tw-mb-6'
                ),
            ],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                },
                {
                    prop: 'contacts.estimatedMissingContacts',
                    values: [isNo, isUnknown, isYes],
                    not: true,
                },
                computed(() => useTaskTableStore().taskCounts.contact === 0),
                computed(() => useContextTableStore().contagiousContextCount === 0),
                {
                    prop: 'groupTransport.withReservedSeats',
                    values: [isNo, isUnknown, isYes],
                    not: true,
                },
            ]
        )
        .appendConfig({ 'outer-class': '' });

export const contactTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        contactsFormInfoSchema(generator),
        contactsSchema(generator),
        contactsCountSchema(generator),
        contactsContagiousPeriodSchema(generator),
        groupTransportSchema(generator),
        contactsBCOInfoSchema(generator),
        contactsInfoSchema(generator),
    ]);
};
