import ContactEditingTable from '@/components/caseEditor/ContactEditingTable/ContactEditingTable.vue';
import ContextEditingTable from '@/components/caseEditor/ContextEditingTable/ContextEditingTable.vue';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { formatDate, parseDate } from '@/utils/date';
import type { AllowedVersions } from '..';
import { add, sub } from 'date-fns';
import {
    contextCategoryV1Options,
    countryV1Options,
    transportationTypeV1Options,
    yesNoUnknownV1Options,
} from '@dbco/enum';
import { allSources, isYes, sourceV1Options } from '../../formOptions';
import { FormConditionRule } from '../../formTypes';
import type { DTO } from '@dbco/schema/dto';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';

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
        ],
        undefined,
        undefined,
        'mt-4'
    );

export const sourceSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.heading('Positief geteste bronpersonen', 'h3', 'mt-0'),
    generator.component(ContactEditingTable, {
        group: 'positivesource',
    }),
    generator.heading('Bronpersonen met coronagerelateerde klachten', 'h3', 'mt-0'),
    generator.component(ContactEditingTable, {
        group: 'symptomaticsource',
    }),
];

export const contextsSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.component(ContextEditingTable, {
        group: 'source',
    }),
];

export const hasLikelySourceEnvironmentsSchema = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>
) => [
    generator
        .field('sourceEnvironments', 'hasLikelySourceEnvironments')
        .radioButtonGroup(
            'Is er een waarschijnlijke plaats van besmetting die niet tot de contexten wordt gerekend?',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('sourceEnvironments', 'likelySourceEnvironments')
                    .presetOptions(
                        'Settings die vaak geen context zijn',
                        'Kies uit alle settings',
                        sourceV1Options,
                        allSources(contextCategoryV1Options)
                    ),
            ]
        ),
];

const abroadSchema = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);
    // Dynamic label which is based on dateOfSymptomOnset

    const groupLabel = () => {
        const fragments: DTO<AllowedVersions['index']> = store.getters['index/fragments'];
        const { dateOfSymptomOnset } = fragments.test;

        if (dateOfSymptomOnset) {
            const minDate = sub(parseDate(dateOfSymptomOnset, 'yyyy-MM-dd'), { days: 14 });
            const maxDate = parseDate(dateOfSymptomOnset, 'yyyy-MM-dd');

            return `Index is in de 14 dagen voor EZD (${formatDate(minDate, 'd MMM')} - ${formatDate(
                maxDate,
                'd MMM'
            )}) in het buitenland geweest`;
        }

        return `Index is in de 14 dagen voor EZD in het buitenland geweest`;
    };

    return [
        generator.field('abroad', 'wasAbroad').radioButtonGroup(
            groupLabel,
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.field('abroad', 'trips').repeatableGroup(
                    '+ Nog een verblijf in het buitenland toevoegen',
                    [
                        generator.group(
                            [
                                SchemaGenerator.orphanField('departureDate')
                                    .datePicker('Datum vertrek')
                                    .appendConfig({
                                        min: formatDate(sub(created_at, { years: 1 }), 'yyyy-MM-dd'),
                                        max: formatDate(created_at, 'yyyy-MM-dd'),
                                        validation: `optional|before:${formatDate(
                                            add(created_at, { days: 1 }),
                                            'yyyy-MM-dd'
                                        )}|after:${formatDate(sub(created_at, { years: 1, days: 1 }), 'yyyy-MM-dd')}`,
                                    }),
                                SchemaGenerator.orphanField('returnDate').datePicker('Datum terugkeer'),
                            ],
                            'container mb-3'
                        ),
                        generator.group(
                            [
                                SchemaGenerator.orphanField('countries').chips(
                                    'Welke land(en)?',
                                    'Kies land(en)',
                                    countryV1Options
                                ),
                                SchemaGenerator.orphanField('transportation').chips(
                                    'Met welke vervoersmiddel(en)?',
                                    'Kies vervoersmiddel(en)',
                                    transportationTypeV1Options
                                ),
                            ],
                            'container'
                        ),
                    ],
                    1
                ),
            ],
            '',
            'd-flex flex-column'
        ),
    ];
};

export const sourceTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        formInfoSchema(generator),
        generator.formChapter(sourceSchema(generator)),
        generator.formChapter(contextsSchema(generator), 'Broncontexten'),
        generator.formChapter(hasLikelySourceEnvironmentsSchema(generator)),
        generator.formChapter(abroadSchema(generator), 'Buitenland'),
    ]);
};
