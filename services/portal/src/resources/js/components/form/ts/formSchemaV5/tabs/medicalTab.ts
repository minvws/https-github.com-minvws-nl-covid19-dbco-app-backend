import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { Children } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { StoreType } from '@/store/storeType';
import { formatDate, parseDate } from '@/utils/date';
import { symptomV1Options, yesNoUnknownV1Options, testReasonV3Options } from '@dbco/enum';
import { add, sub } from 'date-fns';
import type { AllowedVersions } from '..';
import { isNo, isYes } from '../../formOptions';
import * as medicalTabV1 from '../../formSchemaV1/tabs/medicalTab';
import * as medicalTabV4 from '../../formSchemaV4/tabs/medicalTab';
import type { CovidCaseV5 } from '@dbco/schema/covidCase/covidCaseV5';
import type { CovidCaseV6 } from '@dbco/schema/covidCase/covidCaseV6';

export const whichSymptoms = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator.field('symptoms', 'symptoms').checkbox('Welke klachten heeft of had de index?', symptomV1Options, 2);

export const otherSymptoms = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator.group(
        [generator.field('symptoms', 'otherSymptoms').repeatable('Anders, namelijk:', 'Voeg een andere klacht toe', 6)],
        'pt-4 col-12'
    );

export const symptomTiming = (generator: SchemaGenerator<CovidCaseV5 | CovidCaseV6>) =>
    generator.group(
        [
            generator.field('symptoms', 'wasSymptomaticAtTimeOfCall').radioButtonGroup(
                'Heeft de index nu nog isolatieverlengende klachten?',
                yesNoUnknownV1Options,
                [isNo],
                [
                    generator
                        .field('symptoms', 'stillHadSymptomsAt')
                        .datePicker('Tot en met wanneer waren de klachten aanwezig?')
                        .appendConfig({
                            max: formatDate(new Date(), 'yyyy-MM-dd'),
                            validation: `optional|before:${formatDate(add(new Date(), { days: 1 }), 'yyyy-MM-dd')}`,
                        }),
                ],
                'col-12'
            ),
        ],
        'pt-4 col-12'
    );

export const symptomCourse = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator.group(
        [
            generator.info(
                'Als de klachten langer dan 5 dagen aanhouden, wordt de isolatieperiode mogelijk verlengd. Let op de kalender.'
            ),
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

export const symptomsSchema = (
    generator: SchemaGenerator<AllowedVersions['index']>,
    children: Children<CovidCaseV5 | CovidCaseV6>
) => [
    generator
        .field('symptoms', 'hasSymptoms')
        .radioButtonGroup('Heeft of had de index klachten?', yesNoUnknownV1Options, [isYes], children),
];

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
                    .checkbox('Waarom heeft de index zich laten testen?', testReasonV3Options, 2),
            ],
            'row'
        ),
        generator.group([
            generator.field('test', 'otherReason').text('Anders, namelijk', '').appendConfig({ maxlength: 500 }),
        ]),
    ];
};

export const pregnancyRecentBirthScheme = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    return [
        generator
            .field('pregnancy', 'isPregnant')
            .radioButtonGroup(
                'Index is zwanger',
                yesNoUnknownV1Options,
                [isYes],
                [
                    generator
                        .field('pregnancy', 'remarks')
                        .textArea(
                            'Toelichting zwangerschap',
                            'Beschrijf eventuele medische bijzonderheden over de zwangerschap'
                        )
                        .appendConfig({ maxlength: 5000 }),
                ],
                'd-flex flex-column'
            ),
    ];
};

export const medicalTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(
            symptomsSchema(generator, [
                whichSymptoms(generator),
                otherSymptoms(generator),
                symptomTiming(generator),
                symptomCourse(generator),
            ]),
            'Klachten'
        ),
        generator.formChapter(medicalTabV4.testResultsSchema(generator), 'Eerste ziektedag en testen', true, 'p-0'),
        generator.formChapter(testSchema(generator)),
        generator.formChapter(medicalTabV1.provenSchema(generator)),
        generator.formChapter(
            medicalTabV1.vaccinationScheme(generator, [medicalTabV1.infectionHpZoneNumberSchema(generator)]),
            'Bescherming'
        ),
        generator.formChapter(medicalTabV4.indexIsVaccinatedScheme(generator)),
        generator.formChapter(medicalTabV1.hospitalScheme(generator), 'Ziekenhuisopname'),
        generator.formChapter(
            medicalTabV4.underlyingSufferingScheme(generator),
            'Onderliggend lijden en/of verminderde afweer'
        ),
        generator.formChapter(pregnancyRecentBirthScheme(generator), 'Zwangerschap'),
        generator.formChapter(medicalTabV1.generalPractitionerScheme(generator), 'Gegevens huisarts'),
    ]);
};
