import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { StoreType } from '@/store/storeType';
import { formatDate, parseDate } from '@/utils/date';
import { add, sub } from 'date-fns';
import type { AllowedVersions } from '..';
import { testReasonV2Options } from '@dbco/enum';
import { isNo } from '../../formOptions';
import * as medicalTabV1 from '../../formSchemaV1/tabs/medicalTab';
import * as medicalTabV2 from '../../formSchemaV2/tabs/medicalTab';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';

export const testSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);

    return [
        generator.group([
            generator
                .field('test', 'dateOfSymptomOnset')
                .datePicker('Eerste ziektedag (EZD)')
                .appendConfig({
                    min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                    max: formatDate(new Date(), 'yyyy-MM-dd'),
                    validation: `optional|before:${formatDate(
                        add(new Date(), { days: 1 }),
                        'yyyy-MM-dd'
                    )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                })
                .conditionalReadonly(
                    {
                        prop: 'symptoms.hasSymptoms',
                        values: [isNo],
                    },
                    StoreType.INDEX,
                    {
                        tooltip:
                            'Je hebt aangegeven dat de index geen klachten heeft of had. EZD is dan gelijk aan de testdatum',
                        format: 'date',
                    }
                ),
            generator
                .field('test', 'dateOfTest')
                .datePicker('Testdatum')
                .appendConfig({
                    min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                    max: formatDate(new Date(), 'yyyy-MM-dd'),
                    validation: `optional|before:${formatDate(
                        add(new Date(), { days: 1 }),
                        'yyyy-MM-dd'
                    )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                }),
        ]),
        generator.group([
            generator
                .slot(
                    [
                        generator
                            .field('test', 'isSymptomOnsetEstimated')
                            .toggle('EZD is een geschatte datum')
                            .appendConfig({
                                class: 'pb-4',
                            }),
                    ],
                    [
                        {
                            prop: 'symptoms.hasSymptoms',
                            values: [isNo],
                            not: true,
                        },
                    ]
                )
                .appendConfig({
                    class: 'col',
                }),
        ]),
        generator.group(
            [
                generator
                    .field('test', 'dateOfInfectiousnessStart')
                    .readonly(
                        'Eerste besmettelijke dag',
                        undefined,
                        'Deze datum kun je niet aanpassen en is afhankelijk van de eerste ziektedag'
                    )
                    .appendConfig({
                        format: 'date',
                    }),
            ],
            'pb-4'
        ),
        generator.div(
            [
                generator
                    .field('test', 'reasons')
                    .checkbox('Waarom heeft de index zich laten testen?', testReasonV2Options, 2),
            ],
            'row'
        ),
        generator.group([
            generator.field('test', 'otherReason').text('Anders, namelijk', '').appendConfig({ maxlength: 500 }),
        ]),
    ];
};

export const medicalTabSchema = <TModel extends CovidCaseV3>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(medicalTabV1.symptomsSchema(generator), 'Klachten'),
        generator.formChapter(testSchema(generator), 'Eerste ziektedag en test'),
        generator.formChapter(medicalTabV1.provenSchema(generator)),
        generator.formChapter(
            medicalTabV1.vaccinationScheme(generator, [medicalTabV1.infectionHpZoneNumberSchema(generator)]),
            'Bescherming'
        ),
        generator.formChapter(medicalTabV2.indexIsVaccinatedScheme(generator)),
        generator.formChapter(medicalTabV2.immunityScheme(generator)),
        generator.formChapter(medicalTabV1.hospitalScheme(generator), 'Ziekenhuisopname'),
        generator.formChapter(
            medicalTabV1.underlyingSufferingScheme(generator),
            'Onderliggend lijden en/of verminderde afweer'
        ),
        generator.formChapter(medicalTabV1.pregnancyRecentBirthScheme(generator), 'Zwangerschap of recent bevallen'),
        generator.formChapter(medicalTabV1.hasRecentlyGivenBirthScheme(generator)),
        generator.formChapter(medicalTabV1.generalPractitionerScheme(generator), 'Gegevens huisarts'),
    ]);
};
