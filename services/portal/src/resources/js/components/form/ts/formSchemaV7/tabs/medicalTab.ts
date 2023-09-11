import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as medicalTabV1 from '../../formSchemaV1/tabs/medicalTab';
import * as medicalTabV4 from '../../formSchemaV4/tabs/medicalTab';
import * as medicalTabV5 from '../../formSchemaV5/tabs/medicalTab';
import * as medicalTabV6 from '../../formSchemaV6/tabs/medicalTab';
import { standardBCOInfoSchema } from '../../formSchemaV6/shared/bcoType';

export const symptomCourse = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator.group(
        [
            generator
                .field('symptoms', 'diseaseCourse')
                .textArea(
                    'Omschrijving ziekteverloop van de index',
                    'Sinds wanneer verergering, sinds wanneer langzame verbetering? Welke klachten waren in het begin aanwezig, welke klachten zijn er nu nog aanwezig?'
                )
                .appendConfig({ maxlength: 5000 }),
        ],
        'pt-4 col-12'
    );

export const medicalTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(
            medicalTabV5.symptomsSchema(generator, [medicalTabV6.symptoms(generator), symptomCourse(generator)]),
            'Klachten'
        ),
        generator.formChapter(medicalTabV4.testResultsSchema(generator), 'Eerste ziektedag en testen', true, 'p-0'),
        generator.formChapter(medicalTabV5.testSchema(generator)),
        generator.formChapter(medicalTabV1.provenSchema(generator)),
        generator.formChapter(
            medicalTabV1.vaccinationScheme(generator, [medicalTabV6.caseNumberSchema(generator)]),
            'Bescherming'
        ),
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
