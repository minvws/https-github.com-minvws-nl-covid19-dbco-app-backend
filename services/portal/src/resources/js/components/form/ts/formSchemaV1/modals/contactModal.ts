import { add } from 'date-fns';
import type { AllowedVersions } from '..';
import { BsnLookupType, FormConditionRule } from '../../formTypes';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import {
    ContactCategoryV1,
    contactCategoryV1Options,
    emailLanguageV1Options,
    genderV1Options,
    InformedByV1,
    informedByV1Options,
    informTargetV1Options,
    InformTargetV1,
    languageV1Options,
    personalProtectiveEquipmentV1Options,
    relationshipV1Options,
    symptomV1Options,
    taskAdviceV1Options,
    testResultV1Options,
    vaccineV1Options,
    yesNoUnknownV1Options,
    MessageTemplateTypeV1,
    CalendarViewV1,
} from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import { formatDate, parseDate } from '@/utils/date';
import { isMedicalPeriodInfoIncomplete } from '@/utils/case';
import { isNo, isUnknown, isYes, yesNoUnknownV1LangOptions } from '../../formOptions';
import { StoreType } from '@/store/storeType';
import type { TaskV1 } from '@dbco/schema/task/taskV1';
import BsnLookup from '@/components/caseEditor/BsnLookup/BsnLookup.vue';
import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';
import ContactConversationDetails from '@/components/caseEditor/ContactConversationDetails/ContactConversationDetails.vue';
import ContactConversationSendButton from '@/components/caseEditor/ContactConversationSendButton/ContactConversationSendButton.vue';
import MessageBox from '@/components/caseEditor/MessageBox/MessageBox.vue';

import store from '@/store';
import type { TaskV2 } from '@dbco/schema/task/taskV2';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { DTO } from '@dbco/schema/dto';
import type { TaskStoreState } from '@/store/task/taskStore';
import type { IndexStoreState } from '@/store/index/indexStore';
import { generateSafeHtml } from '@/utils/safeHtml';
import type { TaskV3 } from '@dbco/schema/task/taskV3';
import type { TaskV4 } from '@dbco/schema/task/taskV4';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import { isNotNull } from '@dbco/ui-library';

export const closeContactDuringQuarantineFields = (generator: SchemaGenerator<TaskV1 | TaskV2>, isSource = false) => {
    const fields: Children<TaskV1 | TaskV2> = [
        generator
            .field('general', 'category')
            .dropdown('Categorie', 'Kies categorie', contactCategoryV1Options)
            .appendConfig({
                class: 'col-6 w100 p-0',
            }),
    ];

    if (!isSource) {
        fields.push(
            generator.group(
                [
                    generator.slot(
                        [
                            generator.group(
                                [
                                    generator
                                        .field('general', 'closeContactDuringQuarantine')
                                        .toggle('Afstand houden tot index is niet mogelijk tijdens quarantaine'),
                                ],
                                'pb-4',
                                ''
                            ),
                        ],

                        [
                            {
                                prop: 'general.category',
                                values: [
                                    ContactCategoryV1.VALUE_1,
                                    ContactCategoryV1.VALUE_2a,
                                    ContactCategoryV1.VALUE_2b,
                                ],
                            },
                        ],
                        StoreType.TASK
                    ),
                ],
                '',
                'w-100'
            )
        );
    }

    return fields;
};

export const aboutSchema = (generator: SchemaGenerator<TaskV1 | TaskV2>, isSource = false) => {
    const completedAt = store.getters['index/meta'].completedAt;
    const fragments: DTO<AllowedVersions['index']> = store.getters['index/fragments'];

    const fields: Children<TaskV1 | TaskV2> = [
        generator.group([
            generator
                .field('general', 'firstname')
                .text('Voornaam', 'Vul voornaam in')
                .appendConfig({ maxlength: 250 }),
            generator
                .field('general', 'lastname')
                .text('Achternaam', 'Vul achternaam in')
                .appendConfig({ maxlength: 500 }),
        ]),
    ];

    if (isSource) {
        fields.push(
            generator.group(
                [
                    generator
                        .field('general', 'reference')
                        .text('Casenummer (HPZone of BCO Portaal)', 'Vul het casenummer in')
                        .validation(['optional'])
                        .appendConfig({ maxlength: 16 }),
                ],
                'pb-4'
            )
        );
    }

    fields.push(
        generator.group(
            [
                generator
                    .field('general', 'dateOfLastExposure')
                    .customDatePicker(
                        'Laatste contact',
                        isSource
                            ? CalendarViewV1.VALUE_index_task_source_table
                            : CalendarViewV1.VALUE_index_task_contagious_table,
                        completedAt,
                        true,
                        !userCanEdit(),
                        !isMedicalPeriodInfoIncomplete(fragments),
                        [
                            generator.slot(
                                [
                                    generator.info(
                                        'Let op: datums kunnen nog niet gekozen worden wegens ontbrekende medische gegevens in de case.',
                                        true,
                                        12,
                                        'warning',
                                        'px-0 info-block--lg'
                                    ),
                                ],
                                [
                                    {
                                        rule: FormConditionRule.MedicalPeriodInfoIncomplete,
                                    },
                                ]
                            ),
                            generator.slot(
                                [
                                    generator.info(
                                        'De besmettelijke periode van de index kan nog wijzigen. Vul van de index minimaal in: wel/geen klachten en ziekenhuisopname.',
                                        true,
                                        12,
                                        'warning',
                                        'px-0 info-block--lg'
                                    ),
                                ],
                                [
                                    {
                                        rule: FormConditionRule.MedicalPeriodInfoNotDefinitive,
                                    },
                                ]
                            ),
                        ]
                    ),
                generator.div(closeContactDuringQuarantineFields(generator, isSource), 'col-6'),
                generator
                    .field('general', 'context')
                    .textArea('Notitie (optioneel)', 'Bijv. trainer of collega')
                    .appendConfig({ maxlength: 5000 }),
                generator
                    .field('general', 'relationship')
                    .dropdown('Relatie tot index', 'Kies relatie', relationshipV1Options),
                generator.div(
                    [
                        generator.group(
                            [
                                generator.slot(
                                    [
                                        generator.group(
                                            [
                                                generator
                                                    .field('general', 'otherRelationship')
                                                    .text('Anders, namelijk', 'Vul relatie tot index in')
                                                    .appendConfig({ maxlength: 500 }),
                                            ],
                                            'pb-3',
                                            ''
                                        ),
                                    ],
                                    [
                                        {
                                            prop: 'general.relationship',
                                            values: ['other'],
                                        },
                                    ],
                                    StoreType.TASK
                                ),
                            ],
                            '',
                            'w-100'
                        ),
                    ],
                    'container'
                ),
                generator
                    .field('general', 'nature')
                    .textArea('Wat is er tijdens het contact gebeurd?', 'Vul in wat er tijdens het contact gebeurd is')
                    .appendConfig({ maxlength: 5000 }),
            ],
            'pb-4'
        )
    );

    if (isSource) {
        fields.push(
            generator.group([
                generator.field('general', 'isSource').toggle('Deze persoon is een zeer waarschijnlijke bron'),
            ])
        );
    }

    fields.push(
        generator.slot(
            [
                generator.group([
                    generator.info(
                        'Omdat dit contact geen huisgenoot of nauw contact is, is verdere aanvulling van gegevens niet meer nodig.',
                        undefined,
                        undefined,
                        undefined,
                        'py-2'
                    ),
                ]),
            ],
            [
                {
                    prop: 'general.category',
                    values: [ContactCategoryV1.VALUE_3a, ContactCategoryV1.VALUE_3b],
                },
            ],
            StoreType.TASK
        )
    );

    return fields;
};

export const sourcePersonalDetailsSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator.group([
        generator.field('personalDetails', 'dateOfBirth').dateOfBirth('Geboortedatum (DD-MM-JJJJ)'),
        generator.field('personalDetails', 'gender').radioButton('Geslacht', genderV1Options),
    ]),
    generator.field('personalDetails', 'address').addressLookup(),
];

export const circumstancesSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator.field('circumstances', 'wasUsingPPE').radioButtonGroup(
        'Tijdens contact droeg één van de twee mensen persoonlijke beschermingsmiddelen (PBM)',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator
                .field('circumstances', 'ppeMedicallyCompetent')
                .radioBoolean('Is de drager van de PBM medisch bekwaam (zorgmedewerker)?'),
            generator.info(
                'PBM die door een (medisch bekwame) zorgverlener worden gedragen kunnen invloed hebben op de categorie waarin het contact valt. Zie de werkinstructie.'
            ),
            generator
                .field('circumstances', 'usedPersonalProtectiveEquipment')
                .checkbox(
                    'Welke persoonlijke beschermingsmiddelen (PBM) zijn gebruikt?',
                    personalProtectiveEquipmentV1Options,
                    2,
                    '',
                    'Niet medische mondneuskapjes:<br />Type I, Type II<br /><br />Medische mondneuskapjes:<br />Type IIR, FFP1, FFP2'
                ),
            generator.div(
                [
                    generator.slot(
                        [
                            generator.group(
                                [
                                    generator
                                        .field('circumstances', 'ppeType')
                                        .text('Welk type medisch mondkapje is gebruikt?', 'Vul type in')
                                        .appendConfig({ maxlength: 250 }),
                                ],
                                'pb-4'
                            ),
                        ],
                        [
                            {
                                prop: 'circumstances.usedPersonalProtectiveEquipment',
                                values: ['mask'],
                            },
                        ],
                        StoreType.TASK
                    ),
                ],
                'container'
            ),
            generator
                .field('circumstances', 'ppeReplaceFrequency')
                .text('Hoe vaak en wanneer wordt de persoonlijke beschermingsmiddelen gewisseld?')
                .appendConfig({ maxlength: 500 }),
        ]
    ),
];

export const informSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => {
    const task = store.getters['task/fragments'];
    const whoShouldBeInformedChildren: Children<AllowedVersions['task']> = [
        generator.slot(
            [
                generator.div(
                    [
                        generator.div(
                            [
                                generator
                                    .field('alternateContact', 'firstname')
                                    .text('Voornaam vertegenwoordiger')
                                    .appendConfig({ maxlength: 250 }),
                                generator
                                    .field('alternateContact', 'lastname')
                                    .text('Achternaam vertegenwoordiger')
                                    .appendConfig({ maxlength: 500 }),
                            ],
                            'row'
                        ),
                    ],
                    'container'
                ),
            ],
            [
                {
                    prop: 'inform.informTarget',
                    values: [InformTargetV1.VALUE_representative],
                },
            ],
            StoreType.TASK,
            12,
            'mb-0'
        ),
        generator.field('general', 'phone').phone('Mobiel telefoonnummer').appendConfig({ maxlength: 25 }),
        generator.field('general', 'email').email('E-mailadres').appendConfig({ maxlength: 250 }),
        generator.slot(
            [
                generator.div(
                    [
                        generator.div(
                            [
                                generator.field('alternateContact', 'gender').radioButton('Geslacht', genderV1Options),
                                generator
                                    .field('alternateContact', 'relationship')
                                    .dropdown('Relatie tot contact', 'Kies type relatie', relationshipV1Options),
                            ],
                            'row'
                        ),
                    ],
                    'container'
                ),
            ],
            [
                {
                    prop: 'inform.informTarget',
                    values: [InformTargetV1.VALUE_representative],
                },
            ],
            StoreType.TASK
        ),
        generator
            .field('alternativeLanguage', 'useAlternativeLanguage')
            .radioButtonGroup(
                () =>
                    task.inform.informTarget === InformTargetV1.VALUE_representative
                        ? 'Voorkeurstaal van de vertegenwoordiger'
                        : 'Voorkeurstaal van het contact',
                yesNoUnknownV1LangOptions,
                [isYes],
                [
                    generator
                        .field('alternativeLanguage', 'phoneLanguages')
                        .chips('Voorkeurstaal telefonisch contact met GGD', 'Zoeken in lijst', languageV1Options),
                    generator
                        .field('alternativeLanguage', 'emailLanguage')
                        .dropdown('Voorkeurstaal e-mails GGD', 'Kies taal', emailLanguageV1Options),
                ],
                'w-100 container'
            ),
    ];

    return [
        generator
            .field('inform', 'informTarget')
            .radioButtonGroup(
                'Wie moet geïnformeerd worden?',
                informTargetV1Options,
                [InformTargetV1.VALUE_contact, InformTargetV1.VALUE_representative],
                whoShouldBeInformedChildren
            ),
    ];
};

export const givenAdviceSchema = (generator: SchemaGenerator<TaskV1 | TaskV2>, className?: string) => {
    const advice = 'Laat je testen op [ n.v.t. of onvoldoende gegevens bekend ]';

    return [
        generator.div(
            [
                generator.label('Welke adviezen zijn gegeven voor dit contact?'),
                generator.field('inform', 'quarantineAdvice').inputCheckbox('', advice, 'textarea', 12, 'mb-0'),
                generator.field('inform', 'advices').checkbox('', taskAdviceV1Options, 1, '', '', 'mt-2 mb-0'),
                generator.field('inform', 'testAdvice').inputCheckbox('', advice, 'textarea', 12, 'mb-2'),
                generator
                    .field('inform', 'otherAdvice')
                    .textArea(
                        'Andere gegeven adviezen',
                        'Noteer alleen adviezen die je daadwerkelijk gegeven hebt. Denk hierbij ook aan adviezen die je alleen voor deze specifieke situatie hebt gegeven',
                        12,
                        'mt-4'
                    ),
            ],
            className
        ),
    ];
};

export const shareIndexWithContact = (generator: SchemaGenerator<TaskV1 | TaskV2>) => {
    const indexName = store.getters['index/meta'].name;

    const informedByChildren: Children<TaskV1 | TaskV2> = [
        generator.slot(
            [
                generator
                    .field('inform', 'shareIndexNameWithContact')
                    .radioBoolean(
                        'Mag de naam van de index genoemd worden?',
                        'radio-button-wrapper d-flex flex-row justify-content-between align-items-center',
                        'col-6 row'
                    ),
                generator.slot(
                    [
                        generator.info(
                            generateSafeHtml(`<strong>Let op</strong>, je mag de naam van de index niet noemen.`)
                        ),
                    ],
                    [
                        {
                            prop: 'inform.shareIndexNameWithContact',
                            values: [false],
                        },
                    ],
                    StoreType.TASK
                ),
                generator.slot(
                    [generator.info(generateSafeHtml('De index heet <strong>{indexName}</strong>.', { indexName }))],
                    [
                        {
                            prop: 'inform.shareIndexNameWithContact',
                            values: [true],
                        },
                    ],
                    StoreType.TASK
                ),
            ],
            [
                {
                    prop: 'inform.informedBy',
                    values: [InformedByV1.VALUE_staff],
                },
            ],
            StoreType.TASK,
            12,
            'mb-0'
        ),
        generator.slot(
            givenAdviceSchema(generator),
            [
                {
                    prop: 'inform.informedBy',
                    values: [InformedByV1.VALUE_index],
                },
            ],
            StoreType.TASK,
            12,
            'mb-0'
        ),
    ];
    return [
        generator
            .field('inform', 'informedBy')
            .radioButtonGroup(
                'Wie informeert dit contact?',
                informedByV1Options,
                [InformedByV1.VALUE_staff, InformedByV1.VALUE_index],
                informedByChildren,
                'd-flex flex-column'
            ),
    ];
};

export const personalDetailsSchema = (generator: SchemaGenerator<TaskV1 | TaskV2 | TaskV3 | TaskV4>) => {
    return [
        generator
            .div(
                [
                    generator.component(BsnLookup, { targetType: BsnLookupType.Task, disabled: !userCanEdit() }, [
                        generator
                            .field('personalDetails', 'hasNoBsnOrAddress')
                            .toggle('Deze persoon heeft geen BSN of vaste verblijfplaats')
                            .appendConfig({
                                disabled: !userCanEdit(),
                                class: 'p-0',
                            }),
                        generator.slot(
                            [
                                generator
                                    .field('personalDetails', 'bsnNotes')
                                    .textArea(
                                        'Vul zo veel mogelijk informatie in om deze persoon te identificeren',
                                        'Beschrijf de verblijfplaats en/of een eventueel niet nederlands persoonsnummer'
                                    )
                                    .appendConfig({
                                        disabled: !userCanEdit(),
                                        class: 'col-6 p-0 mt-2',
                                    }),
                            ],
                            [
                                {
                                    prop: 'personalDetails.hasNoBsnOrAddress',
                                    values: [true],
                                },
                            ],
                            StoreType.TASK
                        ),
                    ]),
                ],
                'w-100'
            )
            // This is needed to open the "BCO-gesprek" / contact conversation toggle group when hasNoBsnOrAddress has a value.
            // This is necessary because buttonToggleGroup is looking recursively at the children fieldnames to see if there is a value.
            .appendConfig({ name: 'personalDetails.hasNoBsnOrAddress' }),
    ];
};

export const symptomsSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    // The symptoms are not cleared when selecting No or Unknown, this is as desired
    generator.field('symptoms', 'hasSymptoms').radioButtonGroup(
        'Het contact heeft klachten',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.div(
                [
                    generator
                        .field('symptoms', 'dateOfSymptomOnset')
                        .datePicker('Eerste ziektedag (EZD)')
                        .appendConfig({
                            max: formatDate(new Date(), 'yyyy-MM-dd'),
                            validation: `optional|before:${formatDate(add(new Date(), { days: 1 }), 'yyyy-MM-dd')}`,
                        }),
                ],
                'row mb-3'
            ),
            generator.group([
                generator
                    .field('symptoms', 'symptoms')
                    .checkbox('Welke klachten heeft of had het contact?', symptomV1Options, 2),
            ]),
            generator.group(
                [
                    generator
                        .field('symptoms', 'otherSymptoms')
                        .repeatable('Anders, namelijk:', 'Voeg een andere klacht toe'),
                ],
                'pt-4 pb-4'
            ),
        ],
        '',
        'd-flex flex-column'
    ),
];

export const testSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator
        .field('test', 'isTested')
        .radioButtonGroup(
            'Dit contact heeft naar aanleiding van de ontmoeting met de index een coronatest gedaan',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.field('test', 'dateOfTest').datePicker('Testdatum'),
                generator
                    .field('test', 'testResult')
                    .radioButton('Wat was de uitslag van deze coronatest?', testResultV1Options),
            ]
        ),
];

export const reinfectionSchema = (generator: SchemaGenerator<TaskV1 | TaskV2>) => [
    generator.field('test', 'isReinfection').radioButtonGroup(
        'Dit contact is eerder besmet geweest',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.info(
                'Voor contacten die in de afgelopen 365 dagen corona hebben gehad gelden mogelijk afwijkende adviezen.'
            ),
            generator
                .field('test', 'previousInfectionDateOfSymptom')
                .datePicker('(Geschatte) eerste ziektedag vorige besmetting (testdatum als contact asymptomatisch was)')
                .appendConfig({
                    class: 'col-6 w100', // original + mb-2
                    min: '2020-01-01',
                    max: formatDate(new Date(), 'yyyy-MM-dd'),
                    validation: `optional|before:${formatDate(
                        add(new Date(), { days: 1 }),
                        'yyyy-MM-dd'
                    )}|after:2019-12-31`,
                    'validation-messages': {
                        before: ({ args }) =>
                            `EZD vorige besmetting moet voor ${formatDate(parseDate(args), 'dd-MM-yyyy')} zijn.`,
                        after: ({ args }) =>
                            `EZD vorige besmetting moet na ${formatDate(parseDate(args), 'dd-MM-yyyy')} zijn.`,
                    },
                }),
            generator.dateDifferenceLabel(
                'test.previousInfectionDateOfSymptom',
                'test.dateOfTest',
                'de testdatum in dit dossier'
            ),
            generator.info(
                'Is de EZD minder dan 8 weken geleden? Dan vervalt het testadvies omdat de kans op vals positief groot is.'
            ),
            generator.div(
                [
                    generator
                        .field('test', 'previousInfectionReported')
                        .radioButtonGroup(
                            'Is de eerdere besmetting destijds bij de GGD gemeld?',
                            yesNoUnknownV1Options,
                            [isYes],
                            [
                                generator.group([
                                    generator
                                        .field('test', 'previousInfectionHpzoneNumber')
                                        .text('HPZone-nummer vorige besmetting')
                                        .validation('hpZoneRetro', 'HPZone-nummer')
                                        .appendConfig({ maxlength: 8 }),
                                ]),
                            ],
                            '',
                            'w-100'
                        ),
                ],
                'container'
            ),
        ]
    ),
];

const vaccinationSchema = (generator: SchemaGenerator<TaskV1>) => [
    generator.field('vaccination', 'isVaccinated').radioButtonGroup(
        'Dit contact is gevaccineerd',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.field('vaccination', 'vaccineInjections').repeatableGroup(
                'Prik toevoegen',
                [
                    generator.group(
                        [
                            SchemaGenerator.orphanField('injectionDate')
                                .datePicker(
                                    '(Geschatte) datum prik',
                                    '2021-01-01',
                                    formatDate(new Date(), 'yyyy-MM-dd')
                                )
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
                                    maxlength: 500,
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
                                                                    debounce: 300,
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
                                                ],
                                                StoreType.TASK
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
                1
            ),
            generator
                .field('vaccination', 'hasCompletedVaccinationSeries')
                .toggle('Contact heeft vaccinatiereeks voltooid')
                .appendConfig({ class: 'mb-4 font-bold' }),
        ]
    ),
];

export const immunityScheme = (generator: SchemaGenerator<TaskV1 | TaskV2 | TaskV3>) => [
    generator.div(
        [
            generator.label(
                'Baseer je antwoord op de vragen over vaccinatie en eerdere besmettingen. Het contact is voldoende beschermd als een van de onderstaande drie zaken geldt:',
                'mb-2'
            ),
            generator.feedback(
                'Heeft minstens 1 week voor de laatste contactdatum een boostervaccinatie gehad (Pfizer/Moderna) én is daarvoor al volledig gevaccineerd (1 prik Janssen óf 2 prikken Pfizer/Moderna/AstraZeneca)',
                [
                    {
                        rule: FormConditionRule.PotentiallyVaccinated,
                    },
                ],
                StoreType.TASK,
                '',
                'icon--questionmark-feedback'
            ),
            generator.feedback(
                'Heeft minstens 1 week voor de laatste contactdatum een boostervaccinatie gehad (Pfizer/Moderna) én is daarvoor al besmet geweest en 1 keer gevaccineerd',
                [
                    {
                        rule: FormConditionRule.PreviouslyInfectedAndPotentiallyVaccinated,
                    },
                ],
                StoreType.TASK,
                '',
                'icon--questionmark-feedback'
            ),
            generator.feedback(
                'Is besmet geweest na 1 januari 2022',
                [
                    {
                        rule: FormConditionRule.RecentlyInfected,
                    },
                ],
                StoreType.TASK
            ),
            generator.paragraph('Twijfel je? Overleg met de medische supervisie.', 'mb-4'),
            generator.group([
                generator.label(`Was het contact op het laatste contactmoment beschermd?`),
                generator.field('immunity', 'isImmune').radioButton('', yesNoUnknownV1Options),
            ]),
            generator
                .group([generator.field('immunity', 'remarks').textArea('Toelichting (optioneel)')], 'w50 pb-4')
                .appendConfig({ maxlength: 5000 }),
        ],
        'd-flex flex-column'
    ),
];

export const jobSchema = (generator: SchemaGenerator<TaskV1 | TaskV2>) => [
    generator
        .field('job', 'worksInAviation')
        .radioButtonGroup(
            'Dit contact werkt in de luchtvaart (vliegend personeel) of op een internationale veerdienst',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.info(
                    generateSafeHtml(
                        '<strong>Let op</strong>: mogelijk mag dit contact niet werken, ook als quarantaine niet hoeft. Verder kan testen noodzakelijk zijn. Raadpleeg daarom de werkinstructie. '
                    )
                ),
            ],
            ''
        ),
];

export const worksInHealthCareSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator
        .field('job', 'worksInHealthCare')
        .radioButtonGroup(
            'Dit contact werkt in de zorg',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('job', 'healthCareFunction')
                    .text('Wat is de functie van dit contact?', 'Vul functie in'),
                generator.info('Let op: afwijkend beleid. Lees de bijlage zorgmedewerkers van de werkinstructie'),
            ]
        ),
];

export const remarksSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator.group([
        generator
            .field('general', 'remarks')
            .textArea(
                'Opmerkingen en bijzonderheden over het contactgesprek',
                'Bijvoorbeeld: contact werkt wel / niet goed mee, taalbarrière etc.',
                12
            )
            .appendConfig({ maxlength: 5000 }),
    ]),
];

export const hpZoneSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator.group([
        generator
            .field('general', 'reference')
            .text('Heeft dit contact een dossier in HPZone? Vul hier het nummer in', 'Vul HPZone-nummer in')
            .validation(['optional', 'hpZone'], 'HPZone-nummer')
            .appendConfig({ maxlength: 8 }),
    ]),
];

export const contactConversationButtonToggleGroup = (generator: SchemaGenerator<TaskV1>) =>
    generator.buttonToggleGroup(
        'Contactgesprek',
        'Contactgesprek starten',
        [
            generator.formChapter(personalDetailsSchema(generator), 'Identificeren van het contact'),
            generator.formChapter(symptomsSchema(generator), 'COVID-19 status'),
            generator.formChapter(testSchema(generator)),
            generator.formChapter(reinfectionSchema(generator), 'Immuniteit'),
            generator.formChapter(vaccinationSchema(generator)),
            generator.formChapter(immunityScheme(generator)),

            generator.slot(
                [
                    generator.formChapter(jobSchema(generator), 'Opmerkingen en bijzonderheden'),
                    generator.formChapter(worksInHealthCareSchema(generator)),
                ],
                [
                    {
                        prop: 'general.category',
                        values: [ContactCategoryV1.VALUE_1, ContactCategoryV1.VALUE_2a, ContactCategoryV1.VALUE_2b],
                    },
                    {
                        prop: 'inform.informedBy',
                        values: [InformedByV1.VALUE_staff],
                    },
                ],
                StoreType.TASK
            ),
            generator.formChapter(remarksSchema(generator)),
            generator.formChapter(givenAdviceSchema(generator, 'row'), 'Opmerkingen & afgesproken beleid'),
        ],
        ContactConversationDetails,
        ContactConversationSendButton
    );

export const guidelinesSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => {
    const caseUuid: IndexStoreState['uuid'] = store.getters['index/uuid'];
    const taskUuid: TaskStoreState['uuid'] = store.getters['task/uuid'];

    return generator.slot(
        [
            generator.formChapter([
                generator.group(
                    [
                        generator
                            .field('general', 'email')
                            .sendEmail(
                                caseUuid,
                                taskUuid ?? null,
                                MessageTemplateTypeV1.VALUE_contactInfection,
                                'Verstuur adviezen naar contact',
                                'primary'
                            ),
                    ],
                    'ml-3'
                ),
            ]),
        ],
        [
            {
                prop: 'general.category',
                values: [null],
                not: true,
            },
        ],
        StoreType.TASK
    );
};

export const messageBoxSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator.component(MessageBox, {
        taskUuid: store.getters['task/uuid'],
    }),
];

export const sourceContactModalSchema = <TModel extends AllowedVersions['task']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(aboutSchema(generator, true), 'Over het contact'),
        generator.formChapter(sourcePersonalDetailsSchema(generator), 'Persoonsgegevens'),
    ]);
};

export const contactModalSchema = <TModel extends TaskV1>(isSource = false) => {
    const generator = new SchemaGenerator<TModel>();

    const chapters = [
        generator.formChapter(aboutSchema(generator, isSource), 'Over het contact'),

        generator.slot(
            [generator.formChapter(circumstancesSchema(generator))],
            [
                {
                    prop: 'general.category',
                    values: [ContactCategoryV1.VALUE_1, ContactCategoryV1.VALUE_2a, ContactCategoryV1.VALUE_2b],
                },
            ],
            StoreType.TASK
        ),

        generator.formChapter(informSchema(generator), 'Informeren'),
        generator.formChapter(shareIndexWithContact(generator)),

        generator.slot(
            [contactConversationButtonToggleGroup(generator)],
            [
                {
                    prop: 'inform.informedBy',
                    values: [InformedByV1.VALUE_staff],
                },
            ],
            StoreType.TASK
        ),
        generator.formChapter(hpZoneSchema(generator), 'Contactdossier'),
        guidelinesSchema(generator),
    ];

    if (userCanEdit()) {
        chapters.push(generator.formChapter(messageBoxSchema(generator), 'Verzonden berichten'));
    }

    return generator.toConfig(chapters);
};

export const contactModalContagiousSidebarSchema = <TIndex extends CovidCaseV1 | CovidCaseV2>() => {
    const sidebarGenerator = new SchemaGenerator<TIndex>();
    const calendar = useCalendarStore();

    const ranges = [
        ...calendar.getCalendarDataByView(CalendarViewV1.VALUE_index_task_contagious_sidebar),
        calendar.getLastContactDateRange,
    ].filter(isNotNull);

    return sidebarGenerator.toConfig([
        sidebarGenerator.slot(
            [
                sidebarGenerator.info(
                    generateSafeHtml(
                        'Dit contact moet een coronatest doen: <ul><li>Zo snel mogelijk</li><li>5 dagen na de laatste isolatiedag van de index</li><li>Wanneer er klachten ontstaan</li></ul>'
                    ),
                    true,
                    12,
                    'warning',
                    'px-0 info-block--lg'
                ),
            ],
            [
                {
                    prop: 'general.closeContactDuringQuarantine',
                    values: [true],
                },
                {
                    prop: 'general.category',
                    values: [ContactCategoryV1.VALUE_1, ContactCategoryV1.VALUE_2a, ContactCategoryV1.VALUE_2b],
                },
                {
                    prop: 'immunity.isImmune',
                    values: [isNo, isUnknown],
                },
            ],
            StoreType.TASK
        ),
        sidebarGenerator.component(Calendar, {
            class: 'px-0',
            showLegend: true,
            ranges,
        }),
        sidebarGenerator.field('general', 'notes').textArea(undefined, 'Je kunt hier een notitie maken', 12, 'px-0'),
    ]);
};

export const contactModalSourceSidebarSchema = <TIndex extends CovidCaseV1 | CovidCaseV2>() => {
    const sidebarGenerator = new SchemaGenerator<TIndex>();
    const calendar = useCalendarStore();

    const ranges = [
        ...calendar.getCalendarDataByView(CalendarViewV1.VALUE_index_task_contagious_sidebar),
        calendar.getLastContactDateRange,
    ].filter(isNotNull);

    return sidebarGenerator.toConfig([
        sidebarGenerator.component(Calendar, {
            class: 'px-0',
            showLegend: true,
            ranges,
        }),
        sidebarGenerator.field('general', 'notes').textArea(undefined, 'Je kunt hier een notitie maken', 12, 'px-0'),
    ]);
};
