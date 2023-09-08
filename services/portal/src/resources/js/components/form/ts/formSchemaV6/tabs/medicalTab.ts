import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as medicalTabV1 from '../../formSchemaV1/tabs/medicalTab';
import * as medicalTabV4 from '../../formSchemaV4/tabs/medicalTab';
import * as medicalTabV5 from '../../formSchemaV5/tabs/medicalTab';
import { FormConditionRule } from '../../formTypes';
import { standardBCOInfoSchema } from '../shared/bcoType';

export const symptoms = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [medicalTabV5.whichSymptoms(generator), medicalTabV5.otherSymptoms(generator)],
            [
                { rule: FormConditionRule.HasValuesOrExtensiveBCO, prop: 'symptoms.symptoms' },
                { rule: FormConditionRule.HasValuesOrExtensiveBCO, prop: 'symptoms.otherSymptoms' },
            ],
            undefined,
            undefined,
            'mt-0 mb-0',
            'OR'
        )
        .appendConfig({ 'outer-class': '' });

export const caseNumberSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .field('test', 'previousInfectionCaseNumber')
        .text('Dossiernummer vorige besmetting')
        .validation(['optional', 'caseNumber']);

export const medicalTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(
            medicalTabV5.symptomsSchema(generator, [
                symptoms(generator),
                medicalTabV5.symptomTiming(generator),
                medicalTabV5.symptomCourse(generator),
            ]),
            'Klachten'
        ),
        generator.formChapter(medicalTabV4.testResultsSchema(generator), 'Eerste ziektedag en testen', true, 'p-0'),
        generator.formChapter(medicalTabV5.testSchema(generator)),
        generator.formChapter(medicalTabV1.provenSchema(generator)),
        generator.formChapter(medicalTabV1.vaccinationScheme(generator, [caseNumberSchema(generator)]), 'Bescherming'),
        generator.formChapter(medicalTabV4.indexIsVaccinatedScheme(generator)),
        generator.formChapter(medicalTabV1.hospitalScheme(generator), 'Ziekenhuisopname'),
        generator.formChapter(
            medicalTabV4.underlyingSufferingScheme(generator),
            'Onderliggend lijden en/of verminderde afweer'
        ),
        generator.formChapter(medicalTabV5.pregnancyRecentBirthScheme(generator), 'Zwangerschap'),
        generator.formChapter(medicalTabV1.generalPractitionerScheme(generator), 'Gegevens huisarts'),
        standardBCOInfoSchema(generator),
    ]);
};
