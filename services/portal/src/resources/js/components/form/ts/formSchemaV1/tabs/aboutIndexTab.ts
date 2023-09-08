import BsnLookup from '@/components/caseEditor/BsnLookup/BsnLookup.vue';
import ReversePairCase from '@/components/caseEditor/ReversePairCase/ReversePairCase.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import {
    causeOfDeathV1Options,
    emailLanguageV1Options,
    genderV1Options,
    languageV1Options,
    MessageTemplateTypeV1,
    relationshipV1Options,
    yesNoUnknownV1Options,
} from '@dbco/enum';
import { formatDate, parseDate } from '@/utils/date';
import { userCanEdit } from '@/utils/interfaceState';
import { add, sub } from 'date-fns';
import type { AllowedVersions } from '..';
import { isYes } from '../../formOptions';
import type { IndexStoreState } from '@/store/index/indexStore';
import { BsnLookupType } from '../../formTypes';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';

export const contactSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const fields: Children<AllowedVersions['index']> = [
        generator.group([
            generator
                .field('contact', 'phone')
                .phone('Mobiel telefoonnummer', '', 'mb-0')
                .appendConfig({ maxlength: 25 }),
            generator.field('contact', 'email').email('E-mailadres').appendConfig({ maxlength: 250 }),
        ]),
    ];

    const caseUuid: IndexStoreState['uuid'] = store.getters['index/uuid'];

    fields.push(
        generator
            .field('contact', 'email')
            .sendEmail(caseUuid, null, MessageTemplateTypeV1.VALUE_missedPhone, 'Verstuur e-mail bij geen gehoor')
            .appendConfig({ class: 'mt-3' })
    );

    return fields;
};

export const aboutIndexSchema = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>) => [
    generator.component(
        BsnLookup,
        {
            targetType: BsnLookupType.Index,
            disabled: !userCanEdit(),
        },
        [
            generator
                .field('index', 'hasNoBsnOrAddress')
                .toggle('Deze persoon heeft geen BSN of vaste verblijfplaats')
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
                        prop: 'index.hasNoBsnOrAddress',
                        values: [true],
                    },
                ]
            ),
        ]
    ),
];

export const alternateContactSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .field('alternateContact', 'hasAlternateContact')
        .radioButtonGroup(
            'BCO-gesprek is met een ander persoon dan de index',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('alternateContact', 'firstname')
                    .text('Voornaam vertegenwoordiger')
                    .appendConfig({ maxlength: 250 }),
                generator
                    .field('alternateContact', 'lastname')
                    .text('Achternaam vertegenwoordiger')
                    .appendConfig({ maxlength: 500 }),
                generator.field('alternateContact', 'gender').radioButton('Geslacht', genderV1Options),
                generator
                    .field('alternateContact', 'relationship')
                    .dropdown('Relatie tot index', 'Kies type relatie', relationshipV1Options),
                generator.field('alternateContact', 'phone').phone('Telefoonnummer').appendConfig({ maxlength: 25 }),
                generator.field('alternateContact', 'email').email('E-mailadres').appendConfig({ maxlength: 250 }),
                generator
                    .field('alternateContact', 'isDefaultContact')
                    .toggle('Deze persoon is ook bij toekomstig contact met de GGD de vertegenwoordiger'),
            ]
        ),
];

export const alternativeLanguageSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .field('alternativeLanguage', 'useAlternativeLanguage')
        .radioButtonGroup(
            'Index of de vertegenwoordiger communiceert liever in een andere taal dan Nederlands',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('alternativeLanguage', 'phoneLanguages')
                    .chips('Voorkeurstaal telefonisch contact met GGD', 'Zoeken in lijst', languageV1Options),
                generator
                    .field('alternativeLanguage', 'emailLanguage')
                    .dropdown('Voorkeurstaal e-mails GGD', 'Kies taal', emailLanguageV1Options),
            ]
        ),
];

export const deceasedSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);
    return [
        generator.field('deceased', 'isDeceased').radioButtonGroup(
            'Index is overleden',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('deceased', 'deceasedAt')
                    .datePicker('Datum overlijden')
                    .appendConfig({
                        min: formatDate(sub(created_at, { days: 100 }), 'yyyy-MM-dd'),
                        max: formatDate(new Date(), 'yyyy-MM-dd'),
                        validation: `optional|before:${formatDate(
                            add(new Date(), { days: 1 }),
                            'yyyy-MM-dd'
                        )}|after:${formatDate(sub(created_at, { days: 101 }), 'yyyy-MM-dd')}`,
                    }),
                generator
                    .field('deceased', 'cause')
                    .dropdown('Oorzaak overlijden', 'Kies oorzaak', causeOfDeathV1Options),
                generator.info('Vraag bij het overlijden ook onderliggend lijden uit'),
            ]
        ),
    ];
};

export const particularitiesSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.div(
        [
            generator
                .field('communication', 'particularities')
                .textArea(
                    'Opmerkingen en bijzonderheden over het BCO-gesprek',
                    'Bijvoorbeeld: index werkt wel / niet goed mee, taalbarrière etc.',
                    6
                )
                .appendConfig({ maxlength: 5000 }),
        ],
        'row'
    ),
];

export const reversePairSchema = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>
) => [generator.component(ReversePairCase)];

export const aboutIndexTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(contactSchema(generator), 'Contactgegevens'),
        generator.formChapter(aboutIndexSchema(generator), 'Identificeren van de index'),
        generator.formChapter(alternateContactSchema(generator), 'Over het gesprek'),
        generator.formChapter(alternativeLanguageSchema(generator)),
        generator.formChapter(deceasedSchema(generator)),
        generator.formChapter(particularitiesSchema(generator)),
        generator.formChapter(reversePairSchema(generator)),
    ]);
};
