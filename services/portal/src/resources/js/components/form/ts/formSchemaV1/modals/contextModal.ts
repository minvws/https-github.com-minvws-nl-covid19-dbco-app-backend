import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';
import ContextPlace from '@/components/caseEditor/ContextPlace/ContextPlace.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import {
    covidMeasureV1Options,
    personalProtectiveEquipmentV1Options,
    yesNoUnknownV1Options,
    ContextRelationshipV1,
    CalendarViewV1,
} from '@dbco/enum';
import { isYes } from '../../formOptions';
import type { Moment } from '../../formTypes';
import { FormConditionRule } from '../../formTypes';
import { parseDate } from '@/utils/date';
import { startOfDay } from 'date-fns';
import type { AllowedVersions } from '..';
import { userCanEdit } from '@/utils/interfaceState';
import { StoreType } from '@/store/storeType';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import { isNotNull } from '@dbco/ui-library';

const infoSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator.div(
        [
            generator
                .field('general', 'label')
                .text('Omschrijving', 'Vul een omschrijving in')
                .appendConfig({ maxlength: 250 }),
            generator
                .field('general', 'relationship')
                .relationshipDropdown('Relatie tot context', 'Kies relatie tot context'),
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
                                                .text('Anders, namelijk', 'Vul relatie tot context in')
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
                                StoreType.CONTEXT
                            ),
                        ],
                        '',
                        'w-100'
                    ),
                ],
                'container'
            ),
            generator
                .field('general', 'remarks')
                .textArea('Notitie (optioneel)', 'Vul notitie in', 12)
                .appendConfig({ maxlength: 5000 }),
            generator.slot(
                [
                    generator
                        .field('general', 'isSource')
                        .toggle('Deze context is een zeer waarschijnlijke bron (er zijn gerelateerde gevallen)'),
                ],
                [
                    {
                        prop: 'general.moments.*.day',
                        rule: FormConditionRule.DateInSourcePeriod,
                    },
                    {
                        prop: 'general.moments.*.day',
                        values: [],
                        not: true,
                    },
                ],
                StoreType.CONTEXT,
                12,
                'mt-3'
            ),
        ],
        'row'
    ),
];

const placeSelectSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => {
    return [
        generator.component(ContextPlace, { class: 'mb-3', disabled: !userCanEdit() }),
        generator.group([
            generator.slot(
                [
                    generator.info(
                        'De context moet gekoppeld worden voordat je de context specifieke vragen kan beantwoorden',
                        true,
                        12,
                        'warning'
                    ),
                    generator
                        .field('general', 'note')
                        .textArea('Vragen voor deze context', undefined, 12)
                        .appendConfig({ disabled: true }),
                ],
                [
                    {
                        getter: 'place',
                        prop: 'uuid',
                        values: [undefined, null],
                    },
                ],
                StoreType.CONTEXT,
                12,
                'mb-0'
            ),
            generator.slot(
                [
                    generator.label('Vragen voor deze context'),
                    generator.contextCategorySuggestions(),
                    generator
                        .field('general', 'note')
                        .textArea(
                            undefined, // label is added before suggestions
                            undefined,
                            12
                        )
                        .appendConfig({ style: 'height: 208px;', maxlength: 5000 }),
                ],
                [
                    {
                        getter: 'place',
                        prop: 'uuid',
                        values: [undefined, null],
                        not: true,
                    },
                ],
                StoreType.CONTEXT,
                12,
                'mb-0'
            ),
        ]),
    ];
};

const datesPresentSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator.slot(
        [
            generator.info(
                'De bron- en/of besmettelijke periode kunnen nog niet worden getoond. Vul minimaal in: klachten, EZD, testdatum.',
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

    generator.field('general', 'moments').repeatableDateTime({
        calendarView: CalendarViewV1.VALUE_index_context_table,
        rangeCutOff: new Date(),
    }),
];

const covidMeasuresSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator.div(
        [
            generator.group(
                [
                    generator.label(
                        'Zijn er andere mensen met klachten bekend op de locatie? Of mogelijke bronnen? Voeg ze dan toe als contacten.'
                    ),
                    generator.buttonModal('Voeg bronnen en contacten toe', 'ContactsEditingModal'),
                ],
                'pb-4 col-6'
            ),
        ],
        'row'
    ),
    generator.div(
        [
            generator
                .field('circumstances', 'covidMeasures')
                .checkbox('Welke COVID-19 maatregelen worden toegepast?', covidMeasureV1Options, 2),
            generator
                .field('circumstances', 'otherCovidMeasures')
                .repeatable('Anders, namelijk:', 'Beschrijf een andere COVID-19 maatregel'),
        ],
        'row'
    ),
];

const usingPPESchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator.field('circumstances', 'isUsingPPE').radioButtonGroup(
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
                                        .text('Welk type medisch mondkapje is gebruikt?', 'Vul type in'),
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
                        StoreType.CONTEXT
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

const causeForConcernSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator
        .field('circumstances', 'causeForConcern')
        .radioButtonGroup(
            'Er bestaan zorgen over de situatie op locatie',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('circumstances', 'causeForConcernRemark')
                    .textArea('Welke zorgen?', 'Beschrijf zorgen over de situatie op locatie')
                    .appendConfig({ maxlength: 5000 }),
            ],
            'mb-0'
        ),
];

const sharedTransportSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator.slot(
        [
            generator.group([
                generator
                    .field('circumstances', 'sharedTransportation')
                    .radioBoolean('Is er gezamenlijk vervoer van of naar de werklocatie?'),
            ]),
        ],
        [
            {
                prop: 'general.relationship',
                values: ['staff'],
            },
        ],
        StoreType.CONTEXT
    ),
];

const contactPersonSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator.group(
        [
            generator
                .field('contact', 'firstname')
                .text('Voornaam', 'Vul voornaam in')
                .appendConfig({ maxlength: 250 }),
            generator
                .field('contact', 'lastname')
                .text('Achternaam', 'Vul achternaam in')
                .appendConfig({ maxlength: 500 }),
            generator
                .field('contact', 'phone')
                .text('Telefoonnummer', 'Vul telefoonnummer in')
                .appendConfig({ maxlength: 25 }),
        ],
        'pb-3'
    ),
    generator.div(
        [generator.field('contact', 'isInformed').radioBoolean('Is de werkgever / locatie al geïnformeerd?')],
        'row'
    ),
];

const notificationConsentSchema = (generator: SchemaGenerator<AllowedVersions['context']>) => [
    generator
        .field('contact', 'notificationConsent')
        .radioButtonGroup(
            'De GGD mag contact opnemen met locatie',
            yesNoUnknownV1Options,
            [isYes],
            [
                generator
                    .field('contact', 'notificationNamedConsent')
                    .radioBoolean('Mag de naam van de index genoemd worden bij het contact opnemen?'),
            ],
            'mb-0'
        ),
];

export const contextModalSchema = <TModel extends AllowedVersions['context']>() => {
    const generator = new SchemaGenerator<TModel>();
    const general = store.getters['context/fragments'].general;

    const chapters: Children<AllowedVersions['context']> = [
        generator.formChapter(infoSchema(generator), 'Over de context'),
        generator.formChapter(placeSelectSchema(generator), 'Contextgegevens'),
        generator.formChapter(datesPresentSchema(generator), 'Datum(s) aanwezig'),
        generator.formChapter(covidMeasuresSchema(generator), 'Situatie op de locatie'),
        generator.formChapter(usingPPESchema(generator)),
        generator.formChapter(causeForConcernSchema(generator)),
    ];

    if (general?.relationship === ContextRelationshipV1.VALUE_staff) {
        chapters.push(generator.formChapter(sharedTransportSchema(generator)));
    }

    return generator.toConfig([
        ...chapters,
        generator.formChapter(contactPersonSchema(generator), 'Aanspreekpunt locatie'),
        generator.formChapter(notificationConsentSchema(generator)),
    ]);
};

export const contextModalSidebarSchema = <TIndex extends AllowedVersions['index']>() => {
    const sidebarGenerator = new SchemaGenerator<TIndex>();
    const calendar = useCalendarStore();

    const datesPresent = () => {
        const contextFragments = store.getters['context/fragments'];
        if (contextFragments.general.moments) {
            return contextFragments.general.moments
                .filter((date: Moment) => date.day)
                .map((date: Moment) => startOfDay(parseDate(date.day, 'yyyy-MM-dd')));
        }
        return [];
    };

    const ranges = [
        ...calendar.getCalendarDataByView(CalendarViewV1.VALUE_index_context_sidebar),
        ...calendar.getContextVisitRanges(datesPresent()),
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
