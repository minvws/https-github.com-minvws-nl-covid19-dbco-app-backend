import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import {
    eduDaycareTypeV1Options,
    jobSectorGroupV1Options,
    jobSectorV1Options,
    professionCareV1Options,
    professionOtherV1Options,
    riskLocationTypeV1Options,
    yesNoUnknownV1Options,
} from '@dbco/enum';
import { addEmptyOption, isYes } from '../../formOptions';
import { generateSafeHtml } from '@/utils/safeHtml';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';

export const alternateResidencySchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .field('alternateResidency', 'hasAlternateResidency')
        .radioButtonGroup(
            'Index bevindt zich op een ander adres dan thuis',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.field('alternateResidency', 'address').addressLookup(),
                generator.group(
                    [
                        generator
                            .field('alternateResidency', 'remark')
                            .textArea('Toelichting verblijfplaats', 'Vul toelichting in')
                            .appendConfig({ maxlength: 5000 }),
                    ],
                    'pt-2'
                ),
            ],
            '',
            'd-flex flex-column'
        ),
];

export const riskLocationSchema = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>
) => [
    generator.field('riskLocation', 'isLivingAtRiskLocation').radioButtonGroup(
        'Index woont in een instelling, asielzoekerscentrum of op een andere plek waar het virus zich snel kan verspreiden',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.div(
                [
                    generator
                        .field('riskLocation', 'type')
                        .dropdown('Type instelling', 'Kies type', riskLocationTypeV1Options),
                    generator.info(
                        generateSafeHtml(
                            'Verblijft de index in een vluchtelingenopvang georganiseerd door de gemeente? Kies dan <b>Overige maatschappelijke opvang</b>.'
                        )
                    ),
                ],
                'row'
            ),
            generator.slot(
                [
                    generator.group(
                        [generator.field('riskLocation', 'otherType').text('Anders, namelijk', 'Anders, namelijk')],
                        'mt-3'
                    ),
                ],
                [
                    {
                        prop: 'riskLocation.type',
                        values: ['other'],
                    },
                ]
            ),
            generator.div(
                [generator.buttonModal('Deze instelling als context toevoegen', 'ContextsEditingModal')],
                'row'
            ),
        ],
        '',
        'd-flex flex-column'
    ),
];

export const jobEducationSchema = (
    generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>
) => [
    generator.field('job', 'wasAtJob').radioButtonGroup(
        'Index heeft in de 2 weken voor EZD gewerkt (andere locatie dan thuis)',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.group(
                [generator.buttonModal('Voeg indien van toepassing toe als context', 'ContextsEditingModal')],
                'mb-4'
            ),
            generator.group([
                generator
                    .field('job', 'sectors')
                    .multiSelectDropdown(
                        'Sector werk',
                        'Kies sector',
                        jobSectorV1Options,
                        jobSectorGroupV1Options,
                        true
                    ),
            ]),
            generator.slot(
                [
                    generator.group(
                        [
                            generator
                                .field('job', 'professionCare')
                                .dropdown('Wat is het beroep van de index?', 'Kies beroep', professionCareV1Options),
                        ],
                        'pt-4'
                    ),
                ],
                [
                    {
                        prop: 'job.sectors',
                        values: ['20', '21', '22', '23', '24'],
                    },
                ]
            ),
            generator.slot(
                [
                    generator.field('job', 'closeContactAtJob').radioButtonGroup(
                        'Index heeft tijdens werk contact met anderen binnen 1,5 meter afstand',
                        yesNoUnknownV1Options,
                        [isYes],
                        [
                            generator.buttonModal('Voeg toe als contact', 'ContactsEditingModal'),
                            generator.group(
                                [
                                    generator
                                        .field('job', 'professionOther')
                                        .dropdown(
                                            'Wat is het beroep van de index?',
                                            'Kies beroep',
                                            professionOtherV1Options
                                        ),
                                ],
                                'container'
                            ),
                            generator.slot(
                                [
                                    generator.group(
                                        [
                                            generator
                                                .field('job', 'otherProfession')
                                                .text('Anders, namelijk', 'Vul beroep in'),
                                        ],
                                        'container pt-3'
                                    ),
                                ],
                                [
                                    {
                                        prop: 'job.professionOther',
                                        values: ['anders'],
                                    },
                                ]
                            ),
                        ],
                        'pt-4'
                    ),
                ],
                [
                    {
                        prop: 'job.sectors',
                        values: ['13'],
                    },
                ]
            ),
            generator.div(
                [
                    generator
                        .field('job', 'particularities')
                        .textArea(
                            'Bijzonderheden met betrekking tot werksituatie (optioneel)',
                            'Denk bijvoorbeeld aan arbeidsmigranten of gezamenlijk vervoer, etc. ',
                            6
                        )
                        .appendConfig({ maxlength: 5000 }),
                ],
                'row'
            ),
        ],
        '',
        'd-flex flex-column'
    ),
];

export const isStudentScheme = (generator: SchemaGenerator<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4>) => [
    generator
        .field('eduDaycare', 'isStudent')
        .radioButtonGroup(
            'Is de index een student/leerling? (ook deeltijd)',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.buttonModal('Voeg toe als context', 'ContextsEditingModal'),
                generator
                    .field('eduDaycare', 'type')
                    .dropdown('Kies type opleiding', 'Kies type', addEmptyOption(eduDaycareTypeV1Options)),
                generator.info(
                    'Als de index aanwezig was op school, de kinderopvang of bij een gastouder tijdens bron- of besmettelijke periode, voeg deze dan toe als context.'
                ),
            ]
        ),
];

export const housematesSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .field('housemates', 'hasHouseMates')
        .radioButtonGroup(
            'Index heeft huisgenoten',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.div(
                    [
                        generator.buttonModal(
                            'Deze huisgenoten als bronpersonen of contacten toevoegen',
                            'ContactsEditingModal'
                        ),
                    ],
                    'row'
                ),
                generator.heading('Situatie index thuis', 'label', 'container pb-2 mt-3 pl-0'),
                generator.group(
                    [
                        generator.field('housemates', 'hasOwnFacilities').toggle('Index heeft eigen badkamer'),
                        generator.field('housemates', 'hasOwnRestroom').toggle('Index heeft eigen toilet'),
                        generator.field('housemates', 'hasOwnKitchen').toggle('Index heeft eigen keuken'),
                        generator.field('housemates', 'hasOwnBedroom').toggle('Index kan apart slapen van huisgenoten'),
                    ],
                    'pb-3'
                ),
                generator.group(
                    [
                        generator
                            .field('housemates', 'canStrictlyIsolate')
                            .radioBoolean('Index heeft mogelijkheid tot strikte isolatie thuis'),
                    ],
                    'pb-3'
                ),
                generator.group([
                    generator
                        .field('housemates', 'bottlenecks')
                        .textArea(
                            'Welke knelpunten voorziet de index?',
                            'Beschrijf knelpunten en aanvullende afspraken'
                        )
                        .appendConfig({ maxlength: 5000 }),
                ]),
            ],
            '',
            'd-flex flex-column'
        ),
];

export const residenceWorkTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(alternateResidencySchema(generator), 'Woonsituatie'),
        generator.formChapter(riskLocationSchema(generator)),
        generator.formChapter(housematesSchema(generator)),
        generator.formChapter(jobEducationSchema(generator), 'Werk en opleiding'),
        generator.formChapter(isStudentScheme(generator)),
    ]);
};
