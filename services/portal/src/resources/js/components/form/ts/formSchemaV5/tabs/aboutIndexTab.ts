import BsnLookup from '@/components/caseEditor/BsnLookup/BsnLookup.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { BcoTypeV1, bcoTypeV1Options, noBsnOrAddressReasonV1Options } from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import type { AllowedVersions } from '..';
import { BsnLookupType, FormConditionRule } from '../../formTypes';
import * as aboutIndexTabV1 from '../../formSchemaV1/tabs/aboutIndexTab';

const aboutIndexSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.component(
        BsnLookup,
        {
            targetType: BsnLookupType.Index,
            disabled: !userCanEdit(),
        },
        [
            generator
                .field('index', 'hasNoBsnOrAddress')
                .checkbox(
                    'Reden als deze persoon niet geïdentificeerd kan worden',
                    noBsnOrAddressReasonV1Options,
                    undefined,
                    undefined,
                    'Als er meerdere redenen zijn aangevinkt, wordt alleen de bovenste naar Osiris verstuurd.'
                )
                .appendConfig({
                    disabled: !userCanEdit(),
                    class: 'p-0',
                }),
            generator.slot(
                [
                    generator
                        .field('index', 'bsnNotes')
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
                        rule: FormConditionRule.HasValues,
                        prop: 'index.hasNoBsnOrAddress',
                    },
                ]
            ),
        ]
    ),
];

export const extensiveContactTracingSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.group([
        generator
            .field('extensiveContactTracing', 'receivesExtensiveContactTracing')
            .radio('Welk type BCO krijgt de index?', bcoTypeV1Options),
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
    ]),
];

export const aboutIndexTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();
    const chapters: Children<TModel> = [];

    chapters.push(generator.formChapter(extensiveContactTracingSchema(generator)));

    return generator.toConfig([
        ...chapters,
        generator.formChapter(aboutIndexTabV1.contactSchema(generator), 'Contactgegevens'),
        generator.formChapter(aboutIndexSchema(generator), 'Identificeren van de index'),
        generator.formChapter(aboutIndexTabV1.alternateContactSchema(generator), 'Over het gesprek'),
        generator.formChapter(aboutIndexTabV1.alternativeLanguageSchema(generator)),
        generator.formChapter(aboutIndexTabV1.deceasedSchema(generator)),
        generator.formChapter(aboutIndexTabV1.particularitiesSchema(generator)),
    ]);
};
