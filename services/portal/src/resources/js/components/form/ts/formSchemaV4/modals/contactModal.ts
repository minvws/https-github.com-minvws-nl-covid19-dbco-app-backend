import ContactConversationDetails from '@/components/caseEditor/ContactConversationDetails/ContactConversationDetails.vue';
import ContactConversationSendButton from '@/components/caseEditor/ContactConversationSendButton/ContactConversationSendButton.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { StoreType } from '@/store/storeType';
import { ContactCategoryV1, InformedByV1, vaccineV1Options, yesNoUnknownV1Options } from '@dbco/enum';
import { dayBeforeStartDateCovidVaccinations, startDateCovidVaccinations } from '@/utils/constants';
import { formatDate } from '@/utils/date';
import type { TaskV4 } from '@dbco/schema/task/taskV4';
import { add } from 'date-fns';
import type { AllowedVersions } from '..';
import { isYes } from '../../formOptions';
import * as contactModalV1 from '../../formSchemaV1/modals/contactModal';
import * as contactModalV2 from '../../formSchemaV2/modals/contactModal';
import * as contactModalV3 from '../../formSchemaV3/modals/contactModal';

export const vaccinationSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => [
    generator.field('vaccination', 'isVaccinated').radioButtonGroup(
        'Dit contact is gevaccineerd',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.field('vaccination', 'vaccinationCount').number('Hoeveel vaccinaties?', undefined, 'col-2'),
            generator.field('vaccination', 'vaccineInjections').repeatableGroup(
                'Prik toevoegen',
                [
                    generator.group(
                        [
                            SchemaGenerator.orphanField('injectionDate')
                                .datePicker(
                                    'Datum laatste prik',
                                    startDateCovidVaccinations,
                                    formatDate(new Date(), 'yyyy-MM-dd')
                                )
                                .appendConfig({
                                    class: 'col-12',
                                    validation: `optional|before:${formatDate(
                                        add(new Date(), { days: 1 }),
                                        'yyyy-MM-dd'
                                    )}|after:${dayBeforeStartDateCovidVaccinations}`,
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
                                .dropdown('Merk laatste vaccin', 'Kies vaccin', vaccineV1Options)
                                .appendConfig({
                                    class: 'col-12',
                                    maxlength: 500,
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
                                                                    debounce: 300,
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
                        ],
                        'col-4'
                    ),
                    SchemaGenerator.orphanField('isInjectionDateEstimated').toggle('Datum is schatting').appendConfig({
                        class: 'col-4 font-bold label-margin wrapper-border',
                    }),
                ],
                1,
                1
            ),
        ]
    ),
];

export const immunityScheme = (generator: SchemaGenerator<TaskV4>) => [
    generator.div(
        [
            generator.label(
                'Baseer je antwoord op de vragen over vaccinatie en eerdere besmettingen. Het contact is voldoende beschermd als een van de onderstaande drie zaken geldt:',
                'mb-2'
            ),
            generator.ul([
                generator.li(
                    'Heeft minstens 1 week voor de laatste contactdatum een boostervaccinatie gehad (Pfizer/Moderna) én is daarvoor al volledig gevaccineerd (1 prik Janssen óf 2 prikken Pfizer/Moderna/AstraZeneca)'
                ),
                generator.li(
                    'Heeft minstens 1 week voor de laatste contactdatum een boostervaccinatie gehad (Pfizer/Moderna) én is daarvoor al besmet geweest en 1 keer gevaccineerd'
                ),
                generator.li('Is besmet geweest na 1 januari 2022'),
            ]),
            generator.paragraph('Twijfel je? Overleg met de medische supervisie.', 'mb-4'),
            generator.group([
                generator.label(`Was het contact op het laatste contactmoment beschermd?`),
                generator.field('immunity', 'isImmune').radioButton('', yesNoUnknownV1Options),
            ]),
            generator
                .group([generator.field('immunity', 'remarks').textArea('Toelichting (optioneel)')], 'w50 pb-4')
                .appendConfig({ maxlength: 5000 }),
        ],
        'd-flex flex-column'
    ),
];

export const contactConversationButtonToggleGroup = (generator: SchemaGenerator<TaskV4>) =>
    generator.buttonToggleGroup(
        'Contactgesprek',
        'Contactgesprek starten',
        [
            generator.formChapter(contactModalV1.personalDetailsSchema(generator), 'Identificeren van het contact'),
            generator.formChapter(contactModalV1.symptomsSchema(generator), 'COVID-19 status'),
            generator.formChapter(contactModalV1.testSchema(generator)),
            generator.formChapter(
                contactModalV3.reinfectionSchema(generator, [contactModalV3.infectionHpZoneNumberSchema(generator)]),
                'Bescherming'
            ),
            generator.formChapter(vaccinationSchema(generator)),
            generator.formChapter(immunityScheme(generator)),

            generator.slot(
                [
                    generator.formChapter(contactModalV3.jobSchema(generator), 'Opmerkingen en bijzonderheden'),
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
            generator.formChapter(
                contactModalV3.givenAdviceSchema(generator, 'row'),
                'Opmerkingen & afgesproken beleid'
            ),
        ],
        ContactConversationDetails,
        ContactConversationSendButton
    );

export const contactModalSchema = <TModel extends AllowedVersions['task']>(isSource = false) => {
    const generator = new SchemaGenerator<TModel>();

    const chapters: Children<TModel> = [
        generator.formChapter(contactModalV3.aboutSchema(generator, isSource), 'Over het contact'),

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
        generator.formChapter(contactModalV3.shareIndexWithContact(generator)),

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
