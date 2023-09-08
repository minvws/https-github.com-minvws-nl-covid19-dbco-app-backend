import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { yesNoUnknownV1Options, vaccineV1Options } from '@dbco/enum';
import { isYes } from '../../formOptions';
import * as medicalTabV1 from '../../formSchemaV1/tabs/medicalTab';
import { formatDate } from '@/utils/date';
import { add } from 'date-fns';
import { FormConditionRule } from '../../formTypes';
import { StoreType } from '@/store/storeType';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import { generateSafeHtml } from '@/utils/safeHtml';

export const indexIsVaccinatedScheme = (generator: SchemaGenerator<CovidCaseV2 | CovidCaseV3>) => [
    generator.field('vaccination', 'isVaccinated').radioButtonGroup(
        'Index is gevaccineerd',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.field('vaccination', 'vaccineInjections').repeatableGroup(
                '+ Prik toevoegen',
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
                    SchemaGenerator.orphanField('isInjectionDateEstimated').toggle('Datum is schatting').appendConfig({
                        class: 'col-4 font-bold label-margin wrapper-border',
                    }),
                ],
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
                'row'
            ),
        ]
    ),
];

export const immunityScheme = (generator: SchemaGenerator<CovidCaseV2 | CovidCaseV3>) => [
    generator.group(
        [
            generator.label(
                'Baseer je antwoord op de vragen over vaccinatie en eerdere besmettingen. De index is voldoende beschermd als een van de onderstaande drie zaken geldt:',
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
                'Booster ten minste een week voor de testdatum gehad (Pfizer/Moderna) na eerdere besmetting + 1 vaccinatie ',
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

export const medicalTabSchema = <TModel extends CovidCaseV2>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(medicalTabV1.symptomsSchema(generator), 'Klachten'),
        generator.formChapter(medicalTabV1.testSchema(generator), 'Eerste ziektedag en test'),
        generator.formChapter(medicalTabV1.provenSchema(generator)),
        generator.formChapter(
            medicalTabV1.vaccinationScheme(generator, [medicalTabV1.infectionHpZoneNumberSchema(generator)]),
            'Bescherming'
        ),
        generator.formChapter(indexIsVaccinatedScheme(generator)),
        generator.formChapter(immunityScheme(generator)),
        generator.formChapter(medicalTabV1.hospitalScheme(generator), 'Ziekenhuisopname'),
        generator.formChapter(
            medicalTabV1.underlyingSufferingScheme(generator),
            'Onderliggend lijden en/of verminderde afweer'
        ),
        generator.formChapter(medicalTabV1.pregnancyRecentBirthScheme(generator), 'Zwangerschap of recent bevallen'),
        generator.formChapter(medicalTabV1.hasRecentlyGivenBirthScheme(generator)),
        generator.formChapter(medicalTabV1.generalPractitionerScheme(generator), 'Gegevens huisarts'),
    ]);
};
