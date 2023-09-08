import ContactEditingTable from '@/components/caseEditor/ContactEditingTable/ContactEditingTable.vue';
import ContextEditingTable from '@/components/caseEditor/ContextEditingTable/ContextEditingTable.vue';
import PairCase from '@/components/caseEditor/PairCase/PairCase.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { AllowedVersions } from '..';
import { extensiveContactTracingReasonV1Options, yesNoUnknownV1Options } from '@dbco/enum';
import { askedV1Options, isNo, isYes } from '../../formOptions';
import { FormConditionRule } from '../../formTypes';

export const formInfoSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator.slot(
        [
            generator.info(
                'Medische gegevens van index ontbreken. Probeer deze eerst aan te vullen.',
                false,
                12,
                'info',
                'px-0 mb-3'
            ),
        ],
        [
            {
                rule: FormConditionRule.MedicalPeriodInfoIncomplete,
            },
        ]
    );

export const coronaMelderSchema = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2>) => [
    generator
        .field('general', 'askedAboutCoronaMelder')
        .radioCoronaMelder('Index is gevraagd naar gebruik CoronaMelder', askedV1Options),
];

const extensiveContactTracingSchema = (generator: SchemaGenerator<CovidCaseV1>) => [
    generator.field('extensiveContactTracing', 'receivesExtensiveContactTracing').radioButtonGroup(
        'Krijgt deze index uitgebreid BCO?',
        yesNoUnknownV1Options,
        ['', isYes, isNo],
        [
            generator.slot(
                [generator.info('Leg bij deze index alleen categorie 1 contacten (huisgenoten) vast.')],
                [
                    {
                        prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                        values: [isNo],
                    },
                ],
                undefined,
                undefined,
                'm-0'
            ),
            generator.slot(
                [
                    generator.div(
                        [
                            generator
                                .field('extensiveContactTracing', 'reasons')
                                .checkbox(
                                    'Waarom krijgt deze index uitgebreid BCO?',
                                    extensiveContactTracingReasonV1Options,
                                    2
                                ),
                        ],
                        'mb-4'
                    ),
                    generator.div(
                        [
                            generator
                                .field('extensiveContactTracing', 'notes')
                                .textArea(
                                    'Toelichting (optioneel)',
                                    'Bijv. taalbarrière, medische beperking, niet digitaal vaardig. Benoem hier alleen informatie die het type BCO bepaalt.'
                                ),
                        ],
                        'w-50'
                    ),
                ],
                [
                    {
                        prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                        values: [null, isYes],
                    },
                ],
                undefined,
                undefined,
                'my-0'
            ),
            generator.slot(
                [
                    generator.info(
                        'Voor deze index moeten categorie 1 contacten (huisgenoten) en categorie 2 (nauwe contacten) in kaart worden gebracht',
                        undefined,
                        undefined,
                        undefined,
                        'mt-4'
                    ),
                ],
                [
                    {
                        prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                        values: [isYes],
                    },
                ]
            ),
        ]
    ),
];

const contactsSchema = (generator: SchemaGenerator<CovidCaseV1>) => [
    generator.component(ContactEditingTable, {
        group: 'contact',
    }),
    generator
        .div(
            [
                generator
                    .field('contacts', 'estimatedCategory3Contacts')
                    .number(
                        'Hoeveel categorie 3 contacten had de index in totaal? (schatting)',
                        'Categorie 3 contacten (schatting)',
                        'col-6'
                    )
                    .appendConfig({
                        min: '0',
                    })
                    .validation(['numeric', 'zeroOrGreater'], 'Categorie 3 contacten'),
            ],
            'row'
        )
        .appendConfig({
            '@change': true,
        }),
];

export const contactsContagiousPeriodSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.component(ContextEditingTable, {
        group: 'contagious',
    }),
];

export const groupTransportSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .field('groupTransport', 'withReservedSeats')
        .radioButtonGroup(
            'Vliegreis of groepsvervoer met gereserveerde stoelen in besmettelijke periode',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.group([
                    generator.info(
                        'Voeg, indien van toepassing, deze vervoersmiddelen als context toe (zie de werkinstructie).',
                        true,
                        12,
                        'info',
                        'mb-0'
                    ),
                ]),
            ],
            '',
            'd-flex flex-column'
        ),
];

export const pairCaseSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [generator.component(PairCase)];

export const contactTracingTabSchema = <TModel extends CovidCaseV1>() => {
    const generator = new SchemaGenerator<TModel>();
    const chapters: Children<TModel> = [
        formInfoSchema(generator),
        generator.formChapter(coronaMelderSchema(generator)),
    ];

    chapters.push(generator.formChapter(extensiveContactTracingSchema(generator)));

    return generator.toConfig([
        ...chapters,
        generator.formChapter(contactsSchema(generator), 'Contacten binnen besmettelijke periode'),
        generator.formChapter(contactsContagiousPeriodSchema(generator), 'Contexten binnen besmettelijke periode'),
        generator.formChapter(groupTransportSchema(generator), 'Vliegreis of groepsvervoer met gereserveerde stoelen'),
        generator.formChapter(pairCaseSchema(generator), 'GGD Contact'),
    ]);
};
