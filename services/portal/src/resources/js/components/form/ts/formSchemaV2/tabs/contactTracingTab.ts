import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import ContactEditingTable from '@/components/caseEditor/ContactEditingTable/ContactEditingTable.vue';
import * as contactTracingTabV1 from '../../formSchemaV1/tabs/contactTracingTab';
import type { AllowedVersions } from '..';
import { BcoTypeV1, bcoTypeV1Options, extensiveContactTracingReasonV1Options } from '@dbco/enum';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';

export const extensiveContactTracingSchema = (generator: SchemaGenerator<CovidCaseV2 | CovidCaseV3 | CovidCaseV4>) => [
    generator.field('extensiveContactTracing', 'receivesExtensiveContactTracing').radioButtonGroup(
        'Welk type BCO krijgt de index?',
        bcoTypeV1Options,
        ['', BcoTypeV1.VALUE_extensive, BcoTypeV1.VALUE_standard, BcoTypeV1.VALUE_other],
        [
            generator.slot(
                [generator.info('Breng alleen de categorie 1 contacten (huisgenoten) in kaart')],
                [
                    {
                        prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                        values: [BcoTypeV1.VALUE_standard],
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
                                .checkbox('Reden(en) voor uitgebreid BCO', extensiveContactTracingReasonV1Options, 2),
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
                        values: [null, BcoTypeV1.VALUE_extensive],
                    },
                ],
                undefined,
                undefined,
                'my-0'
            ),
            generator.slot(
                [
                    generator.div(
                        [
                            generator
                                .field('extensiveContactTracing', 'otherDescription')
                                .textArea(
                                    'Licht toe hoe het BCO voor deze index wordt ingevuld',
                                    'Bijv. een combinatie van standaard en uitgebreid BCO of een extern uitgevoerd BCO'
                                ),
                        ],
                        'w-50'
                    ),
                ],
                [
                    {
                        prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                        values: [BcoTypeV1.VALUE_other],
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
                        values: [BcoTypeV1.VALUE_extensive],
                    },
                ]
            ),
        ]
    ),
];

export const contactsSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.component(ContactEditingTable, {
        group: 'contact',
    }),
];

export const contactTracingTabSchema = <TModel extends CovidCaseV2>() => {
    const generator = new SchemaGenerator<TModel>();

    const chapters: Children<TModel> = [
        contactTracingTabV1.formInfoSchema(generator),
        generator.formChapter(contactTracingTabV1.coronaMelderSchema(generator)),
    ];

    chapters.push(generator.formChapter(extensiveContactTracingSchema(generator)));

    return generator.toConfig([
        ...chapters,
        generator.formChapter(contactsSchema(generator), 'Contacten binnen besmettelijke periode'),
        generator.formChapter(
            contactTracingTabV1.contactsContagiousPeriodSchema(generator),
            'Contexten binnen besmettelijke periode'
        ),
        generator.formChapter(
            contactTracingTabV1.groupTransportSchema(generator),
            'Vliegreis of groepsvervoer met gereserveerde stoelen'
        ),
        generator.formChapter(contactTracingTabV1.pairCaseSchema(generator), 'GGD Contact'),
    ]);
};
