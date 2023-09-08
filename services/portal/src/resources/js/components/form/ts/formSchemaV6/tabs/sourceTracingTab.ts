import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as sourceTracingTabV1 from '../../formSchemaV1/tabs/sourceTracingTab';
import * as sourceTracingTabV5 from '../../formSchemaV5/tabs/sourceTracingTab';
import { standardBCOInfoSchema } from '../shared/bcoType';
import ContactEditingTable from '@/components/caseEditor/ContactEditingTable/ContactEditingTable.vue';
import { BcoTypeV1 } from '@dbco/enum';
import { computed } from 'vue';
import { useTaskTableStore } from '@/store/task/taskTableStore/taskTableStore';

export const sourceSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [generator.formChapter(sourceSchemaTables(generator))],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                    not: true,
                },
                computed(() => useTaskTableStore().taskCounts.positivesource !== 0),
                computed(() => useTaskTableStore().taskCounts.symptomaticsource !== 0),
            ],
            undefined,
            undefined,
            undefined,
            'OR'
        )
        .appendConfig({ 'outer-class': '' });

export const sourceSchemaTables = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .slot(
            [
                generator.heading('Positief geteste bronpersonen', 'h3', 'mt-0'),
                generator.component(ContactEditingTable, {
                    group: 'positivesource',
                }),
            ],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                    not: true,
                },
                computed(() => useTaskTableStore().taskCounts.positivesource !== 0),
            ],
            undefined,
            undefined,
            undefined,
            'OR'
        )
        .appendConfig({ 'outer-class': '' }),
    generator
        .slot(
            [
                generator.heading('Bronpersonen met coronagerelateerde klachten', 'h3', 'mt-0'),
                generator.component(ContactEditingTable, {
                    group: 'symptomaticsource',
                }),
            ],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                    not: true,
                },
                computed(() => useTaskTableStore().taskCounts.symptomaticsource !== 0),
            ],
            undefined,
            undefined,
            undefined,
            'OR'
        )
        .appendConfig({ 'outer-class': '' }),
];

export const sourceTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        sourceTracingTabV1.formInfoSchema(generator),
        sourceSchema(generator),
        generator.formChapter(sourceTracingTabV1.contextsSchema(generator), 'Broncontexten'),
        generator.formChapter(sourceTracingTabV5.abroadSchema(generator), 'Buitenland'),
        standardBCOInfoSchema(generator),
    ]);
};
