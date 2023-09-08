import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';
import ContactConversationDetails from '@/components/caseEditor/ContactConversationDetails/ContactConversationDetails.vue';
import ContactConversationSendButton from '@/components/caseEditor/ContactConversationSendButton/ContactConversationSendButton.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { TaskV3 } from '@dbco/schema/task/taskV3';
import store from '@/store';
import { StoreType } from '@/store/storeType';
import {
    CalendarViewV1,
    ContactCategoryV1,
    contactCategoryV1Options,
    InformedByV1,
    informedByV1Options,
    relationshipV1Options,
    taskAdviceV2Options,
    yesNoUnknownV1Options,
} from '@dbco/enum';
import { isMedicalPeriodInfoIncomplete } from '@/utils/case';
import { formatDate, formatDateLong, parseDate } from '@/utils/date';
import { userCanEdit } from '@/utils/interfaceState';
import type { DTO } from '@dbco/schema/dto';
import { generateSafeHtml } from '@/utils/safeHtml';
import { add, addDays } from 'date-fns';
import type { AllowedVersions } from '..';
import { isYes } from '../../formOptions';
import * as contactModalV1 from '../../formSchemaV1/modals/contactModal';
import * as contactModalV2 from '../../formSchemaV2/modals/contactModal';
import { FormConditionRule } from '../../formTypes';
import type { TaskV4 } from '@dbco/schema/task/taskV4';
import type { TaskV5 } from '@dbco/schema/task/taskV5';
import type { TaskV6 } from '@dbco/schema/task/taskV6';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import { isNotNull } from '@dbco/ui-library';

const closeContactDuringQuarantineFields = (generator: SchemaGenerator<AllowedVersions['task']>, isSource = false) => {
    const fields: Children<AllowedVersions['task']> = [
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
                                        .toggle('Afstand houden tot index is niet mogelijk'),
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

export const aboutSchema = (generator: SchemaGenerator<AllowedVersions['task']>, isSource = false) => {
    const completedAt = store.getters['index/meta'].completedAt;
    const fragments: DTO<AllowedVersions['index']> = store.getters['index/fragments'];

    const fields: Children<AllowedVersions['task']> = [
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
                        .validation(['optional', 'hpZone'], 'HPZone-nummer')
                        .appendConfig({ maxlength: 8 }),
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

const getContactAdvice = (dateOfLastExposure?: Date | null, closeContactDuringQuarantine?: boolean | null) => {
    let advice = 'Blijf t/m 10 dagen na het laatste contactmoment met de index uit de buurt van kwetsbaren';
    // If contact can not keep distance during quarantaine, do not show specific date
    if (dateOfLastExposure && !closeContactDuringQuarantine) {
        const dateOfLastExposurePlusTen = addDays(dateOfLastExposure, 10);
        advice = `Blijf t/m ${formatDateLong(dateOfLastExposurePlusTen)} uit de buurt van kwetsbaren`;
    }
    return advice;
};

export const givenAdviceSchema = (
    generator: SchemaGenerator<TaskV3 | TaskV4 | TaskV5 | TaskV6>,
    className?: string
) => {
    const taskFragments: DTO<AllowedVersions['task']> = store.getters[`${StoreType.TASK}/fragments`];
    const dateOfLastExposure = taskFragments.general.dateOfLastExposure;
    const closeContactDuringQuarantine = taskFragments.general.closeContactDuringQuarantine;

    const parsedDateOfLastExposure = dateOfLastExposure ? new Date(dateOfLastExposure) : null;
    const vulnerableGroupsAdvice = getContactAdvice(parsedDateOfLastExposure, closeContactDuringQuarantine);

    return [
        generator.div(
            [
                generator.label('Welke adviezen zijn gegeven voor dit contact?'),
                generator.field('inform', 'advices').checkbox('', taskAdviceV2Options, 1, '', '', 'mt-2 mb-0'),
                generator
                    .field('inform', 'vulnerableGroupsAdvice')
                    .inputCheckbox('', vulnerableGroupsAdvice, 'textarea', 12, 'mb-0'),
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

export const shareIndexWithContact = (
    generator: SchemaGenerator<AllowedVersions['task']>,
    givenAdviceSchemaGenerator: typeof givenAdviceSchema = givenAdviceSchema
) => {
    const indexName = store.getters['index/meta'].name;

    const informedByChildren: Children<AllowedVersions['task']> = [
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
                            generateSafeHtml('<strong>Let op</strong>, je mag de naam van de index niet noemen.')
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
            givenAdviceSchemaGenerator(generator),
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

export const infectionHpZoneNumberSchema = (generator: SchemaGenerator<TaskV3 | TaskV4 | TaskV5>) =>
    generator
        .field('test', 'previousInfectionHpzoneNumber')
        .text('HPZone-nummer vorige besmetting')
        .validation('hpZoneRetro', 'HPZone-nummer')
        .appendConfig({ maxlength: 8 });

export const reinfectionSchema = (
    generator: SchemaGenerator<AllowedVersions['task']>,
    previousInfectionReportedSchema: Children<AllowedVersions['task']>
) => [
    generator.field('test', 'isReinfection').radioButtonGroup(
        'Dit contact is eerder besmet geweest',
        yesNoUnknownV1Options,
        [isYes],
        [
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
                            [generator.group(previousInfectionReportedSchema)],
                            '',
                            'w-100'
                        ),
                ],
                'container'
            ),
        ]
    ),
];

export const jobSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator
        .field('job', 'worksInAviation')
        .radioButtonGroup(
            'Dit contact werkt in de luchtvaart (vliegend personeel) of op een internationale veerdienst',
            yesNoUnknownV1Options,
            [],
            [],
            ''
        ),
];

export const contactConversationButtonToggleGroup = (generator: SchemaGenerator<TaskV3>) =>
    generator.buttonToggleGroup(
        'Contactgesprek',
        'Contactgesprek starten',
        [
            generator.formChapter(contactModalV1.personalDetailsSchema(generator), 'Identificeren van het contact'),
            generator.formChapter(contactModalV1.symptomsSchema(generator), 'COVID-19 status'),
            generator.formChapter(contactModalV1.testSchema(generator)),
            generator.formChapter(
                reinfectionSchema(generator, [infectionHpZoneNumberSchema(generator)]),
                'Bescherming'
            ),
            generator.formChapter(contactModalV2.vaccinationSchema(generator)),
            generator.formChapter(contactModalV1.immunityScheme(generator)),

            generator.slot(
                [
                    generator.formChapter(jobSchema(generator), 'Opmerkingen en bijzonderheden'),
                    generator.formChapter(contactModalV1.worksInHealthCareSchema(generator)),
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
            generator.formChapter(contactModalV1.remarksSchema(generator)),
            generator.formChapter(givenAdviceSchema(generator, 'row'), 'Opmerkingen & afgesproken beleid'),
        ],
        ContactConversationDetails,
        ContactConversationSendButton
    );

export const contactModalSchema = <TModel extends AllowedVersions['task']>(isSource = false) => {
    const generator = new SchemaGenerator<TModel>();

    const chapters: Children<TModel> = [
        generator.formChapter(aboutSchema(generator, isSource), 'Over het contact'),

        generator.slot(
            [generator.formChapter(contactModalV1.circumstancesSchema(generator))],
            [
                {
                    prop: 'general.category',
                    values: [ContactCategoryV1.VALUE_1, ContactCategoryV1.VALUE_2a, ContactCategoryV1.VALUE_2b],
                },
            ],
            StoreType.TASK
        ),

        generator.formChapter(contactModalV1.informSchema(generator), 'Informeren'),
        generator.formChapter(shareIndexWithContact(generator, givenAdviceSchema)),

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
        generator.formChapter(contactModalV1.hpZoneSchema(generator), 'Contactdossier'),
        contactModalV2.guidelinesSchema(generator),
    ];

    chapters.push(generator.formChapter(contactModalV1.messageBoxSchema(generator), 'Verzonden berichten'));

    return generator.toConfig(chapters);
};

export const contactModalContagiousSidebarSchema = <TIndex extends AllowedVersions['index']>() => {
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

export const contactModalSourceSidebarSchema = <TIndex extends AllowedVersions['index']>() => {
    const sidebarGenerator = new SchemaGenerator<TIndex>();
    const calendar = useCalendarStore();

    const ranges = [
        ...calendar.getCalendarDataByView(CalendarViewV1.VALUE_index_task_source_sidebar),
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
