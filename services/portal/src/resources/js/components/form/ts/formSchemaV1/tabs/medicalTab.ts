import store from '@/store';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import {
    yesNoUnknownV1Options,
    symptomV1Options,
    testReasonV1Options,
    infectionIndicatorV1Options,
    selfTestIndicatorV1Options,
    labTestIndicatorV1Options,
    vaccinationGroupV1Options,
    vaccineV1Options,
    hospitalReasonV1Options,
    underlyingSufferingV1Options,
    testResultV1Options,
} from '@dbco/enum';
import { isNo, isYes } from '../../formOptions';
import { FormConditionRule } from '../../formTypes';
import type { AllowedVersions } from '..';
import { formatDate, parseDate } from '@/utils/date';
import { add, sub } from 'date-fns';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';
import { StoreType } from '@/store/storeType';
import { generateSafeHtml } from '@/utils/safeHtml';
import type { CovidCaseV5 } from '@dbco/schema/covidCase/covidCaseV5';

export const symptomsSchema = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>) => [
    generator.field('symptoms', 'hasSymptoms').radioButtonGroup(
        'Heeft of had de index klachten?',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator
                .field('symptoms', 'symptoms')
                .checkbox('Welke klachten heeft of had de index?', symptomV1Options, 2),
            generator.group(
                [
                    generator
                        .field('symptoms', 'otherSymptoms')
                        .repeatable('Anders, namelijk:', 'Voeg een andere klacht toe', 6),
                ],
                'pt-4 col-12'
            ),
            generator.group(
                [
                    generator.field('symptoms', 'wasSymptomaticAtTimeOfCall').radioButtonGroup(
                        'Zijn de klachten nu nog aanwezig?',
                        yesNoUnknownV1Options,
                        [isNo],
                        [
                            generator
                                .field('symptoms', 'stillHadSymptomsAt')
                                .datePicker('Tot en met wanneer waren de klachten aanwezig?')
                                .appendConfig({
                                    max: formatDate(new Date(), 'yyyy-MM-dd'),
                                    validation: `optional|before:${formatDate(
                                        add(new Date(), { days: 1 }),
                                        'yyyy-MM-dd'
                                    )}`,
                                }),
                        ],
                        'col-12'
                    ),
                ],
                'pt-4 col-12'
            ),
            generator.group(
                [
                    generator.info(
                        'Als de klachten langer dan 5 dagen aanhouden, wordt de isolatieperiode mogelijk verlengd. Let op de kalender.'
                    ),
                    generator
                        .field('symptoms', 'diseaseCourse')
                        .textArea(
                            'Omschrijving ziekteverloop',
                            'Sinds wanneer verergering, sinds wanneer langzame verbetering? Welke klachten waren in het begin aanwezig, welke klachten zijn er nu nog aanwezig?'
                        )
                        .appendConfig({ maxlength: 5000 }),
                ],
                'pt-4 col-12'
            ),
        ]
    ),
];

export const testSchema = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2>) => {
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
                    .checkbox('Waarom heeft de index zich laten testen?', testReasonV1Options, 2),
            ],
            'row'
        ),
        generator.group([
            generator.field('test', 'otherReason').text('Anders, namelijk', '').appendConfig({ maxlength: 500 }),
        ]),
    ];
};

export const provenSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);

    return [
        generator.field('test', 'infectionIndicator').radioButtonGroup(
            'Hoe is COVID-19 (als eerste) aangetoond?',
            infectionIndicatorV1Options,
            ['selfTest', 'labTest'],
            [
                generator.slot(
                    [
                        generator.info('Vul de datum van de positieve zelftest in bij ‘Testdatum’'),
                        generator
                            .field('test', 'selfTestIndicator')
                            .radio(
                                'Heeft de index vanwege de zelftest een bevestigingstest (laboratorium) gedaan?',
                                selfTestIndicatorV1Options
                            ),
                        generator.slot(
                            [
                                generator.group(
                                    [
                                        generator
                                            .field('test', 'selfTestLabTestDate')
                                            .datePicker('(Geplande) datum laboratoriumtest (als bekend)')
                                            .appendConfig({
                                                min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                                                max: formatDate(add(new Date(), { days: 14 }), 'yyyy-MM-dd'),
                                                validation: `optional|before:${formatDate(
                                                    add(new Date(), { days: 15 }),
                                                    'yyyy-MM-dd'
                                                )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                                            }),
                                        generator
                                            .field('test', 'selfTestLabTestResult')
                                            .radioButton(
                                                'Wat was de uitslag van deze bevestigingstest?',
                                                testResultV1Options
                                            ),
                                    ],
                                    '',
                                    'd-flex w-100'
                                ),
                                generator.slot(
                                    [
                                        generator.info(
                                            'Bij een negatieve bevestigingstest wordt deze persoon niet gezien als index. Overleg met medische supervisie.'
                                        ),
                                    ],
                                    [
                                        {
                                            prop: 'test.selfTestLabTestResult',
                                            values: ['negative'],
                                        },
                                    ],
                                    undefined,
                                    undefined,
                                    'mt-4'
                                ),
                            ],
                            [
                                {
                                    prop: 'test.selfTestIndicator',
                                    values: ['molecular', 'antigen', 'planned-retest'],
                                },
                            ],
                            undefined,
                            undefined,
                            'mt-4'
                        ),
                    ],
                    [
                        {
                            prop: 'test.infectionIndicator',
                            values: ['selfTest'],
                        },
                    ],
                    undefined,
                    undefined,
                    'mb-0'
                ),
                generator.slot(
                    [
                        generator
                            .field('test', 'labTestIndicator')
                            .radio('Welk type test is gebruikt?', labTestIndicatorV1Options),
                        generator.div(
                            [
                                generator.slot(
                                    [
                                        generator.group([
                                            generator
                                                .field('test', 'otherLabTestIndicator')
                                                .text('Anders, namelijk', '')
                                                .appendConfig({ maxlength: 500 }),
                                        ]),
                                    ],
                                    [
                                        {
                                            prop: 'test.labTestIndicator',
                                            values: ['other'],
                                        },
                                    ]
                                ),
                            ],
                            'container'
                        ),
                    ],
                    [
                        {
                            prop: 'test.infectionIndicator',
                            values: ['labTest'],
                        },
                    ]
                ),
            ],
            'pb-0'
        ),
    ];
};

export const infectionHpZoneNumberSchema = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4 | CovidCaseV5>
) =>
    generator
        .field('test', 'previousInfectionHpzoneNumber')
        .text('HPZone-nummer vorige besmetting')
        .validation('hpZoneRetro', 'HPZone-nummer')
        .appendConfig({ maxlength: 8 });

export const vaccinationScheme = (
    generator: SchemaGenerator<AllowedVersions['index']>,
    previousInfectionReportedSchema: Children<AllowedVersions['index']>
) => [
    generator.field('test', 'isReinfection').radioButtonGroup(
        'Index is eerder besmet geweest',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator
                .field('test', 'previousInfectionDateOfSymptom')
                .datePicker('EZD vorige besmetting (testdatum als index asymptomatisch was)')
                .appendConfig({
                    class: 'col-6 w100 mb-2', // original + mb-2
                    min: '2020-01-01',
                    max: formatDate(new Date(), 'yyyy-MM-dd'),

                    validation: `optional|before:${formatDate(
                        add(new Date(), { days: 1 }),
                        'yyyy-MM-dd'
                    )}|after:2019-12-31`,
                    'validation-messages': {
                        before: ({ args }: any) =>
                            `EZD vorige besmetting moet voor ${formatDate(
                                parseDate(args, 'yyyy-MM-dd'),
                                'dd-MM-yyyy'
                            )} zijn.`,
                        after: ({ args }: any) =>
                            `EZD vorige besmetting moet na ${formatDate(
                                parseDate(args, 'yyyy-MM-dd'),
                                'dd-MM-yyyy'
                            )} zijn.`,
                    },
                }),
            generator
                .field('test', 'previousInfectionSymptomFree')
                .radioBoolean('Is de index in de tussentijd klachtenvrij geweest?')
                .appendConfig({
                    class: 'w100 col-6 pl-0 pr-0 mb-2', // original + mb-2
                }),
            generator.dateDifferenceLabel(
                'test.previousInfectionDateOfSymptom',
                'test.dateOfTest',
                'de testdatum in dit dossier'
            ),
            generator.info(
                'Een positieve test wijst op een herbesmetting als de EZD van de (mogelijke) eerdere besmetting minstens 8 weken (56 dagen) voor de nieuwe positieve test ligt. Ook moet de index in de tussentijd klachtenvrij zijn geweest.'
            ),
            generator
                .field('test', 'previousInfectionProven')
                .radioButtonGroup(
                    'Is de eerdere besmetting bewezen met een test?',
                    yesNoUnknownV1Options,
                    [isNo],
                    [
                        generator.group([
                            generator
                                .field('test', 'contactOfConfirmedInfection')
                                .radioBoolean(
                                    'Was de index destijds een categorie 1 of 2 contact van een persoon met een bevestigde besmetting?'
                                ),
                            generator.info(
                                'Bij een mogelijke herbesmetting is de eerste besmetting destijds niet aangetoond, maar had de index wel symptomen én was de index een nauw contact of huisgenoot van een bewezen besmet persoon.'
                            ),
                        ]),
                    ],
                    'container',
                    'd-flex flex-column'
                ),
            generator
                .field('test', 'previousInfectionReported')
                .radioButtonGroup(
                    'Is de eerdere besmetting destijds bij de GGD gemeld?',
                    yesNoUnknownV1Options,
                    [isYes],
                    [generator.group(previousInfectionReportedSchema, 'w-100')],
                    'container',
                    'd-flex flex-column'
                ),
        ]
    ),
];

const indexIsVaccinatedScheme = (generator: SchemaGenerator<CovidCaseV1>) => [
    generator.field('vaccination', 'hasReceivedInvite').radioButtonGroup(
        'Index is gevaccineerd of heeft een afspraak gepland',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.group(
                [
                    generator
                        .field('vaccination', 'groups')
                        .checkbox('Tot welke groep behoort de index?', vaccinationGroupV1Options),
                    generator.linebreak('ml-3 mr-3 w-100'),
                    generator
                        .field('vaccination', 'otherGroup')
                        .text('Anders, namelijk:', 'Anders, namelijk')
                        .appendConfig({ maxlength: 500 }),
                ],
                'pb-4',
                'd-flex flex-column'
            ),
            generator.info(
                generateSafeHtml(
                    '<b>Let op:</b> Heeft de index nog een vaccinatie-afspraak gepland staan? Dan moet die mogelijk worden uitgesteld. Zie voor de termijnen de werkinstructie.'
                )
            ),
            generator.field('vaccination', 'isVaccinated').radioButtonGroup(
                'Index is gevaccineerd',
                yesNoUnknownV1Options,
                [isYes],
                [
                    generator.field('vaccination', 'vaccineInjections').repeatableGroup(
                        'Prik toevoegen',
                        [
                            generator.group(
                                [
                                    SchemaGenerator.orphanField('injectionDate')
                                        .datePicker('Datum prik', '2021-01-01', formatDate(new Date(), 'yyyy-MM-dd'))
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
                                        .dropdown('Met welk vaccin?', 'Kies vaccin', vaccineV1Options)
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
                            SchemaGenerator.orphanField('isInjectionDateEstimated')
                                .toggle('Datum is schatting')
                                .appendConfig({
                                    class: 'col-4 font-bold label-margin wrapper-border',
                                }),
                        ],
                        1
                    ),
                    generator
                        .field('vaccination', 'hasCompletedVaccinationSeries')
                        .toggle('Index heeft vaccinatiereeks voltooid')
                        .appendConfig({ class: 'mb-4 font-bold' }),
                    generator.div(
                        [
                            generator.info(
                                generateSafeHtml(
                                    'Er gelden mogelijk afwijkende adviezen voor indexen die minstens 2 weken (Pfizer/Moderna/AstraZeneca) of 4 weken (Janssen) geleden hun <b>volledige vaccinatieserie</b> hebben afgerond, of minimaal 1 vaccinatie én eerder corona hebben gehad. Raadpleeg de werkinstructie.'
                                )
                            ),
                        ],
                        'row'
                    ),
                ],
                'container',
                'd-flex flex-column'
            ),
        ]
    ),
];

const immunityScheme = (generator: SchemaGenerator<CovidCaseV1>) => [
    generator.group(
        [
            generator.label(
                'Baseer je antwoord op de vragen over vaccinatie en eerdere besmettingen. De index voldoende beschermd als een van deze punten geldt:',
                'w-100 mb-2'
            ),
            generator.feedback(
                'Booster ten minste een week voor de testdatum gehad (Pfizer/Moderna) na 1 vaccinatie met Janssen of na 2 vaccinaties met Pfizer/Moderna/AstraZeneca',
                [
                    {
                        rule: FormConditionRule.PotentiallyVaccinated,
                    },
                ],
                StoreType.INDEX,
                '',
                'icon--questionmark-feedback'
            ),
            generator.feedback(
                'Booster ten minste een week voor de testdatum gehad (Pfizer/Moderna) na eerdere besmetting + 1 vaccinatie',
                [
                    {
                        rule: FormConditionRule.PreviouslyInfectedAndPotentiallyVaccinated,
                    },
                ],
                StoreType.INDEX,
                '',
                'icon--questionmark-feedback'
            ),
            generator.feedback('Besmet geweest na 1 januari 2022', [
                {
                    rule: FormConditionRule.RecentlyInfected,
                },
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

export const hospitalScheme = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);

    return [
        generator.field('hospital', 'isAdmitted').radioButtonGroup(
            'Index is in het ziekenhuis opgenomen',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.group([generator.buttonModal('Voeg toe als context', 'ContextsEditingModal')], 'pb-4'),
                generator.group([
                    generator.field('hospital', 'name').text('Naam ziekenhuis').appendConfig({ maxlength: 500 }),
                    generator
                        .field('hospital', 'location')
                        .text('Locatie (wanneer het ziekenhuis meer dan één locatie heeft)')
                        .appendConfig({ maxlength: 250 }),
                ]),
                generator.group([
                    generator
                        .field('hospital', 'admittedAt')
                        .datePicker('Datum eerste ziekenhuisopname')
                        .appendConfig({
                            min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                            max: formatDate(new Date(), 'yyyy-MM-dd'),
                            validation: `optional|before:${formatDate(
                                add(new Date(), { days: 1 }),
                                'yyyy-MM-dd'
                            )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                        }),
                    generator
                        .field('hospital', 'releasedAt')
                        .datePicker('Datum ontslag (als van toepassing)')
                        .appendConfig({
                            min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                            max: formatDate(new Date(), 'yyyy-MM-dd'),
                            validation: `optional|before:${formatDate(
                                add(new Date(), { days: 1 }),
                                'yyyy-MM-dd'
                            )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                        }),
                ]),
                generator.group(
                    [
                        generator
                            .field('hospital', 'reason')
                            .dropdown('Reden van ziekenhuisopname', 'Kies reden', hospitalReasonV1Options),
                        generator.info(
                            'De isolatieduur voor indexen die vanwege COVID-19 in het ziekenhuis zijn opgenomen, is 10 dagen vanaf de eerste ziektedag.'
                        ),
                    ],
                    'pb-4'
                ),
                generator
                    .field('hospital', 'hasGivenPermission')
                    .radioButtonGroup(
                        'Index heeft toestemming gegeven voor overleg met behandelaar',
                        yesNoUnknownV1Options,
                        [isYes],
                        [
                            generator.group([
                                generator
                                    .field('hospital', 'practitioner')
                                    .text('Naam behandelaar (indien bekend)', 'Vul naam in')
                                    .appendConfig({ maxlength: 500 }),
                                generator
                                    .field('hospital', 'practitionerPhone')
                                    .text('Telefoonnummer behandelaar (indien bekend)', 'Vul telefoonnummer in')
                                    .appendConfig({ maxlength: 25 }),
                            ]),
                        ],
                        '',
                        'w-100'
                    ),
                generator.field('hospital', 'isInICU').radioButtonGroup(
                    'Index is op de intensive care opgenomen',
                    yesNoUnknownV1Options,
                    [isYes],
                    [
                        generator.group(
                            [
                                generator
                                    .field('hospital', 'admittedInICUAt')
                                    .datePicker('Datum opname intensive care')
                                    .appendConfig({
                                        min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                                        max: formatDate(new Date(), 'yyyy-MM-dd'),
                                        validation: `optional|before:${formatDate(
                                            add(new Date(), { days: 1 }),
                                            'yyyy-MM-dd'
                                        )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                                    }),
                            ],
                            'pb-4'
                        ),
                    ],
                    '',
                    'w-100'
                ),
            ],
            '',
            'd-flex flex-column'
        ),
    ];
};

export const underlyingSufferingScheme = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3>) => {
    const genericWarningItems = ['blood-disease', 'malignity', 'kidney', 'transplant', 'immune-deficiency'];

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
                                        underlyingSufferingV1Options,
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

                        // Extra malignity warning
                        generator.group([
                            generator.slot(
                                [
                                    generator.info(
                                        'Noteer of de index behandeld is aan de kanker of kwaadaardige tumor in de afgelopen 3 maanden.'
                                    ),
                                    { component: 'div' }, // Make sure info is not the last child, which doesn't get margin-bottom
                                ],
                                [
                                    {
                                        prop: 'underlyingSuffering.items',
                                        values: ['malignity'],
                                    },
                                ]
                            ),
                        ]),

                        // Extra kidney warning
                        generator.group([
                            generator.slot(
                                [
                                    generator.info('Noteer of er sprake is van (pre)dialyse voor de nieraandoening.'),
                                    { component: 'div' }, // Make sure info is not the last child, which doesn't get margin-bottom
                                ],
                                [
                                    {
                                        prop: 'underlyingSuffering.items',
                                        values: ['kidney'],
                                    },
                                ]
                            ),
                        ]),

                        // Extra immune-deficiency warning
                        generator.group([
                            generator.slot(
                                [
                                    generator.info('Bij HIV: noteer het CD4 getal van de index.'),
                                    { component: 'div' }, // Make sure info is not the last child, which doesn't get margin-bottom
                                ],
                                [
                                    {
                                        prop: 'underlyingSuffering.items',
                                        values: ['immune-deficiency'],
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

export const pregnancyRecentBirthScheme = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>
) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);

    return [
        generator.field('pregnancy', 'isPregnant').radioButtonGroup(
            'Index is zwanger',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('pregnancy', 'dueDate')
                    .datePicker('Uitgerekende datum')
                    .appendConfig({
                        min: formatDate(sub(created_at, { days: 30 }), 'yyyy-MM-dd'),
                        max: formatDate(add(created_at, { months: 10 }), 'yyyy-MM-dd'),
                        validation: `optional|before:${formatDate(
                            add(created_at, { months: 10 }),
                            'yyyy-MM-dd'
                        )}|after:${formatDate(sub(created_at, { days: 31 }), 'yyyy-MM-dd')}`,
                    }),
            ],
            'd-flex flex-column'
        ),
    ];
};

export const hasRecentlyGivenBirthScheme = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>
) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);
    return [
        generator.field('recentBirth', 'hasRecentlyGivenBirth').radioButtonGroup(
            'Index is in de afgelopen 6 weken bevallen',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.group(
                    [
                        generator
                            .field('recentBirth', 'birthDate')
                            .datePicker('Datum bevalling')
                            .appendConfig({
                                min: formatDate(sub(created_at, { weeks: 6 }), 'yyyy-MM-dd'),
                                max: formatDate(created_at, 'yyyy-MM-dd'),
                                validation: `optional|before:${formatDate(
                                    add(created_at, { days: 1 }),
                                    'yyyy-MM-dd'
                                )}|after:${formatDate(sub(created_at, { weeks: 6, days: 1 }), 'yyyy-MM-dd')}`,
                            }),
                    ],
                    'mb-4'
                ),
                generator.group([
                    generator
                        .field('recentBirth', 'birthRemarks')
                        .textArea(
                            'Toelichting bevalling',
                            'Waar is de index bevallen, wie zijn er betrokken (verloskundige, ziekenhuis, kraamzorg).'
                        )
                        .appendConfig({ maxlength: 5000 }),
                ]),
            ],
            '',
            'd-flex flex-column'
        ),
    ];
};

export const generalPractitionerScheme = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.group(
        [
            generator
                .field('generalPractitioner', 'name')
                .text('Naam huisarts', 'Vul naam in')
                .appendConfig({ maxlength: 250 }),
            generator
                .field('generalPractitioner', 'practiceName')
                .text('Naam praktijk', 'Vul naam in')
                .appendConfig({ maxlength: 250 }),
        ],
        'pt-3'
    ),
    generator.field('generalPractitioner', 'address').addressLookup(),
    generator.group(
        [
            generator
                .field('generalPractitioner', 'hasInfectionNotificationConsent')
                .radioBoolean(
                    'Mogen we de huisarts zo nodig benaderen voor medisch overleg of informeren over de besmetting met COVID-19?'
                ),
        ],
        'pt-4'
    ),
];

export const medicalTabSchema = <TModel extends CovidCaseV1>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(symptomsSchema(generator), 'Klachten'),
        generator.formChapter(testSchema(generator), 'Eerste ziektedag en test'),
        generator.formChapter(provenSchema(generator)),
        generator.formChapter(vaccinationScheme(generator, [infectionHpZoneNumberSchema(generator)]), 'Immuniteit'),
        generator.formChapter(indexIsVaccinatedScheme(generator)),
        generator.formChapter(immunityScheme(generator)),
        generator.formChapter(hospitalScheme(generator), 'Ziekenhuisopname'),
        generator.formChapter(underlyingSufferingScheme(generator), 'Onderliggend lijden en/of verminderde afweer'),
        generator.formChapter(pregnancyRecentBirthScheme(generator), 'Zwangerschap of recent bevallen'),
        generator.formChapter(hasRecentlyGivenBirthScheme(generator)),
        generator.formChapter(generalPractitionerScheme(generator), 'Gegevens huisarts'),
    ]);
};
