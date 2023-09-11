import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as residenceWorkTabV1 from '../../formSchemaV1/tabs/residenceWorkTab';
import * as residenceWorkTabV5 from '../../formSchemaV5/tabs/residenceWorkTab';
import { FormConditionRule } from '../../formTypes';
import { standardBCOInfoSchema } from '../shared/bcoType';

export const housematesSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .slot(
            [generator.formChapter(residenceWorkTabV1.housematesSchema(generator))],
            [
                {
                    rule: FormConditionRule.HasValuesOrExtensiveBCO,
                    prop: 'housemates.hasHouseMates',
                },
            ]
        )
        .appendConfig({ 'outer-class': '' }),
];

export const residenceWorkTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(residenceWorkTabV1.alternateResidencySchema(generator), 'Woonsituatie'),
        generator.formChapter(residenceWorkTabV5.riskLocationSchema(generator)),
        generator.div(housematesSchema(generator)),
        generator.formChapter(residenceWorkTabV5.jobEducationSchema(generator), 'Werk', false),
        standardBCOInfoSchema(generator),
    ]);
};
