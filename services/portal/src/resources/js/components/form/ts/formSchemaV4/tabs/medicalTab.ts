import TestResults from '@/components/caseEditor/TestResults/TestResults.vue';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import * as medicalTabV1 from '../../formSchemaV1/tabs/medicalTab';
import * as medicalTabV3 from '../../formSchemaV3/tabs/medicalTab';
import {
    UnderlyingSufferingV2,
    underlyingSufferingV2Options,
    yesNoUnknownV1Options,
    vaccineV1Options,
} from '@dbco/enum';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';
import { isYes } from '../../formOptions';
import type { AllowedVersions } from '..';
import { formatDate } from '@/utils/date';
import { add } from 'date-fns';
import { sortByValue } from '@/utils/object';
import { generateSafeHtml } from '@/utils/safeHtml';

export const testResultsSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.component(TestResults),
];

export const indexIsVaccinatedScheme = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.field('vaccination', 'isVaccinated').radioButtonGroup(
        'Index is gevaccineerd',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.field('vaccination', 'vaccinationCount').number('Hoeveel vaccinaties?', undefined, 'col-2'),
            generator.field('vaccination', 'vaccineInjections').repeatableGroup(
                '+ Prik toevoegen',
                [
                    generator.group(
                        [
                            SchemaGenerator.orphanField('injectionDate')
                                .datePicker('Datum laatste prik', '2020-12-01', formatDate(new Date(), 'yyyy-MM-dd'))
                                .appendConfig({
                                    class: 'col-12',
                                    validation: `optional|before:${formatDate(
                                        add(new Date(), { days: 1 }),
                                        'yyyy-MM-dd'
                                    )}|after:2020-11-30`,
                                }),
                            generator
                                .dateDifferenceLabel('vaccination.vaccineInjections.>.injectionDate')
                                .appendConfig({ class: 'ml-3 mt-2' }),
                        ],
                        'col-4'
                    ),
                    generator.group(
                        [
                            SchemaGenerator.orphanField('vaccineType')
                                .dropdown('Merk laatste vaccin', 'Kies vaccin', vaccineV1Options)
                                .appendConfig({
                                    class: 'col-12',
                                }),
                            generator.div(
                                [
                                    generator.group(
                                        [
                                            generator.slot(
                                                [
                                                    generator.group(
                                                        [
                                                            SchemaGenerator.orphanField('otherVaccineType')
                                                                .text(undefined, 'Naam vaccin')
                                                                .appendConfig({
                                                                    class: 'mt-3 col-12',
                                                                    maxlength: 500,
                                                                }),
                                                        ],
                                                        '',
                                                        ''
                                                    ),
                                                ],
                                                [
                                                    {
                                                        prop: 'vaccination.vaccineInjections.>.vaccineType',
                                                        values: ['other'],
                                                    },
                                                ]
                                            ),
                                        ],
                                        '',
                                        'w-100'
                                    ),
                                ],
                                'container'
                            ),
                        ],
                        'col-4'
                    ),
                    SchemaGenerator.orphanField('isInjectionDateEstimated').toggle('Datum is schatting').appendConfig({
                        class: 'col-4 font-bold label-margin wrapper-border',
                    }),
                ],
                1,
                1
            ),
            generator.div(
                [
                    generator.info(
                        generateSafeHtml(
                            '<strong>Let op:</strong> Heeft de index nog een vaccinatie-afspraak gepland staan? Dan moet die mogelijk worden uitgesteld. Zie voor de termijnen de werkinstructie.'
                        )
                    ),
                ],
                'w-100'
            ),
        ]
    ),
];

export const immunityScheme = (generator: SchemaGenerator<CovidCaseV4>) => [
    generator.group(
        [
            generator.label(
                'Baseer je antwoord op de vragen over vaccinatie en eerdere besmettingen. De index is voldoende beschermd als een van de onderstaande drie zaken geldt:',
                'w-100 mb-2'
            ),
            generator.ul([
                generator.li(
                    'Booster ten minste een week voor de testdatum gehad (Pfizer/Moderna) na 1 vaccinatie met Janssen of na 2 vaccinaties met Pfizer/Moderna/AstraZeneca'
                ),
                generator.li(
                    'Booster ten minste een week voor de testdatum gehad (Pfizer/Moderna) na eerdere besmetting + 1 vaccinatie '
                ),
                generator.li('Besmet geweest na 1 januari 2022'),
            ]),
            generator.paragraph('Twijfel je? Overleg met de medische supervisie.', 'mb-4'),
            generator.group(
                [
                    generator.label('Is de index beschermd?'),
                    generator.field('immunity', 'isImmune').radioButton('', yesNoUnknownV1Options),
                ],
                '',
                ''
            ),
            generator
                .group([generator.field('immunity', 'remarks').textArea('Toelichting (optioneel)')], 'w50 pb-4')
                .appendConfig({ maxlength: 5000 }),
            generator.group(
                [generator.info('Een index moet altijd in isolatie, ook als de index beschermd is.')],
                'w50 pb-4'
            ),
        ],
        '',
        'd-flex flex-column'
    ),
];

export const underlyingSufferingScheme = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const genericWarningItems = [
        UnderlyingSufferingV2.VALUE_immune_disorders,
        UnderlyingSufferingV2.VALUE_hiv_untreated,
        UnderlyingSufferingV2.VALUE_autoimmune_disease,
        UnderlyingSufferingV2.VALUE_solid_tumor,
        UnderlyingSufferingV2.VALUE_organ_stemcell_transplant,
        UnderlyingSufferingV2.VALUE_kidney_dialysis,
        UnderlyingSufferingV2.VALUE_malignant_blood_disease,
    ];
    return [
        generator.field('underlyingSuffering', 'hasUnderlyingSufferingOrMedication').radioButtonGroup(
            'Onderliggend lijden of medicijngebruik',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.field('underlyingSuffering', 'hasUnderlyingSuffering').radioButtonGroup(
                    'Index heeft een onderliggend lijden',
                    yesNoUnknownV1Options,
                    [isYes],
                    [
                        generator.group(
                            [
                                generator
                                    .field('underlyingSuffering', 'items')
                                    .checkbox(
                                        'Welk onderliggend lijden heeft de index?',
                                        sortByValue(underlyingSufferingV2Options),
                                        2
                                    ),
                                generator
                                    .field('underlyingSuffering', 'otherItems')
                                    .repeatable('Anders, namelijk:', 'Voeg ander onderliggend lijden toe'),
                            ],
                            'pb-4'
                        ),

                        // No indication for reduced immune system
                        generator.group([
                            generator.slot(
                                [
                                    generator.info(
                                        'De gekozen aandoeningen geven geen indicatie voor een verminderde afweer.'
                                    ),
                                    { component: 'div' }, // Make sure info is not the last child, which doesn't get margin-bottom
                                ],
                                [
                                    {
                                        // Something is selected
                                        prop: 'underlyingSuffering.items',
                                        values: [],
                                        not: true,
                                    },
                                    {
                                        // None of the items which show a warning are selected
                                        prop: 'underlyingSuffering.items',
                                        values: genericWarningItems,
                                        not: true,
                                    },
                                    {
                                        // No otherItems are added
                                        prop: 'underlyingSuffering.otherItems',
                                        values: [],
                                    },
                                ]
                            ),
                        ]),

                        // Any of these items show a generic warning
                        generator.group([
                            generator.slot(
                                [
                                    generator.info(
                                        'Index heeft mogelijk een aandoening die invloed heeft op het afweersysteem. Overleg met medische supervisie.'
                                    ),
                                    { component: 'div' }, // Make sure info is not the last child, which doesn't get margin-bottom
                                ],
                                [
                                    {
                                        prop: 'underlyingSuffering.items',
                                        values: genericWarningItems,
                                    },
                                ]
                            ),
                        ]),

                        // Other items are added, also show the generic warning
                        generator.group([
                            generator.slot(
                                [
                                    generator.info(
                                        'Index heeft mogelijk een aandoening die invloed heeft op het afweersysteem. Overleg met medische supervisie.'
                                    ),
                                    { component: 'div' }, // Make sure info is not the last child, which doesn't get margin-bottom
                                ],
                                [
                                    {
                                        prop: 'underlyingSuffering.otherItems',
                                        values: [],
                                        not: true,
                                    },
                                    {
                                        // Don't show the generic warning if the previous slot alreay showed the generic warning
                                        prop: 'underlyingSuffering.items',
                                        values: genericWarningItems,
                                        not: true,
                                    },
                                ]
                            ),
                        ]),
                        generator
                            .field('underlyingSuffering', 'remarks')
                            .textArea('Toelichting onderliggend lijden', 'Toelichting', 6, 'w-50 pl-0 pr-0')
                            .appendConfig({ maxlength: 5000 }),
                    ],
                    'container',
                    'd-flex flex-column'
                ),
                generator.info(
                    generateSafeHtml(
                        '<strong>Let op:</strong> Onderstaande vragen hoef je in de meeste situaties niet te stellen. Raadpleeg de werkinstructie voor meer informatie.'
                    )
                ),
                generator
                    .field('medication', 'hasMedication')
                    .radioButtonGroup(
                        'Index gebruikt medicijnen',
                        yesNoUnknownV1Options,
                        [isYes],
                        [
                            generator.group([
                                generator
                                    .field('medication', 'medicines')
                                    .medicinePicker('Welke medicijnen gebruikt de index?'),

                                generator.buttonLink(
                                    'Zoek medicijnen op in GGD Tools Medicatiecheck',
                                    'https://ggdtools.nl/medicatiecheck/',
                                    12
                                ),

                                generator.info(
                                    generateSafeHtml(
                                        'Overleg met een arts als het effect van niet alle medicijnen bekend is. <b>Mogelijk beïnvloeden ze het afweersysteem</b> van de index.'
                                    )
                                ),
                            ]),
                        ],
                        'container',
                        'd-flex flex-column'
                    ),
                generator
                    .field('medication', 'hasGivenPermission')
                    .radioButtonGroup(
                        'Index heeft toestemming gegeven voor overleg met behandelend arts',
                        yesNoUnknownV1Options,
                        [isYes],
                        [
                            generator.group([
                                generator
                                    .field('medication', 'practitioner')
                                    .text('Naam behandelend arts (indien bekend)', 'Vul naam in')
                                    .appendConfig({ maxlength: 500 }),
                                generator
                                    .field('medication', 'practitionerPhone')
                                    .text('Telefoonnummer behandelend arts (indien bekend)', 'Vul telefoonnummer in')
                                    .appendConfig({ maxlength: 25 }),
                                generator
                                    .field('medication', 'hospitalName')
                                    .text('Behandelend ziekenhuis (indien bekend)', 'Vul naam in')
                                    .appendConfig({ maxlength: 300 }),
                            ]),
                        ],
                        'container',
                        'd-flex flex-column'
                    ),
                generator
                    .field('medication', 'isImmunoCompromised')
                    .radioButtonGroup(
                        'Heeft de index een verminderde afweer?',
                        yesNoUnknownV1Options,
                        [isYes],
                        [
                            generator.group([
                                generator
                                    .field('medication', 'immunoCompromisedRemarks')
                                    .textArea('Toelichting verminderde afweer', 'Beschrijf situatie index')
                                    .appendConfig({ maxlength: 5000 }),
                            ]),
                        ],
                        'container',
                        'd-flex flex-column'
                    ),
            ]
        ),
    ];
};

export const medicalTabSchema = <TModel extends CovidCaseV4>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(medicalTabV1.symptomsSchema(generator), 'Klachten'),
        generator.formChapter(testResultsSchema(generator), 'Eerste ziektedag en testen', true, 'p-0'),
        generator.formChapter(medicalTabV3.testSchema(generator)),
        generator.formChapter(medicalTabV1.provenSchema(generator)),
        generator.formChapter(
            medicalTabV1.vaccinationScheme(generator, [medicalTabV1.infectionHpZoneNumberSchema(generator)]),
            'Bescherming'
        ),
        generator.formChapter(indexIsVaccinatedScheme(generator)),
        generator.formChapter(immunityScheme(generator)),
        generator.formChapter(medicalTabV1.hospitalScheme(generator), 'Ziekenhuisopname'),
        generator.formChapter(underlyingSufferingScheme(generator), 'Onderliggend lijden en/of verminderde afweer'),
        generator.formChapter(medicalTabV1.pregnancyRecentBirthScheme(generator), 'Zwangerschap of recent bevallen'),
        generator.formChapter(medicalTabV1.hasRecentlyGivenBirthScheme(generator)),
        generator.formChapter(medicalTabV1.generalPractitionerScheme(generator), 'Gegevens huisarts'),
    ]);
};
