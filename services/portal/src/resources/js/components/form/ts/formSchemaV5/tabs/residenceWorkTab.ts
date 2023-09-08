import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import { generateSafeHtml } from '@/utils/safeHtml';
import {
    jobSectorGroupV1Options,
    jobSectorV1Options,
    professionCareV1Options,
    professionOtherV1Options,
    riskLocationTypeV1Options,
    yesNoUnknownV1Options,
} from '@dbco/enum';
import { isYes } from '../../formOptions';
import * as residenceWorkTabV1 from '../../formSchemaV1/tabs/residenceWorkTab';

export const riskLocationSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
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
            generator.div(
                [
                    generator
                        .field('riskLocation', 'hasRelatedSickness')
                        .radioButtonGroup(
                            'Zijn er gerelateerde ziektegevallen in de instelling?',
                            yesNoUnknownV1Options,
                            [isYes],
                            [
                                generator
                                    .field('symptoms', 'diseaseCourse')
                                    .textArea(
                                        'Omschrijving ziekteverloop van index',
                                        'Sinds wanneer verergering, sinds wanneer langzame verbetering? Welke klachten waren in het begin aanwezig, welke klachten zijn er nu nog aanwezig?'
                                    )
                                    .appendConfig({ maxlength: 5000 }),
                                generator.group(
                                    [
                                        generator
                                            .field('riskLocation', 'hasDifferentDiseaseCourse')
                                            .radioButton(
                                                'Is het ziekteverloop (mogelijk) afwijkend?',
                                                yesNoUnknownV1Options
                                            ),
                                    ],
                                    'col-12 pb-3'
                                ),
                                generator.info(
                                    'Vul op het tabblad medisch het ziekteverloop en eventuele afwijkingen in.'
                                ),
                            ],
                            'col-12'
                        ),
                ],
                'pt-4 row'
            ),
        ],
        '',
        'd-flex flex-column'
    ),
];

export const jobEducationSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.info(
        'Bij standaard BCO: vul deze vraag alleen in als de index in de zorg werkt.',
        undefined,
        undefined,
        undefined,
        'mb-3 px-0'
    ),

    generator.formChapter([
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
                                    .dropdown(
                                        'Wat is het beroep van de index?',
                                        'Kies beroep',
                                        professionCareV1Options
                                    ),
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
    ]),
];

export const residenceWorkTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        generator.formChapter(residenceWorkTabV1.alternateResidencySchema(generator), 'Woonsituatie'),
        generator.formChapter(riskLocationSchema(generator)),
        generator.formChapter(residenceWorkTabV1.housematesSchema(generator)),
        generator.formChapter(jobEducationSchema(generator), 'Werk', false),
    ]);
};
