import ContactConversationDetails from '@/components/caseEditor/ContactConversationDetails/ContactConversationDetails.vue';
import ContactConversationSendButton from '@/components/caseEditor/ContactConversationSendButton/ContactConversationSendButton.vue';
import type { AllowedVersions } from '..';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import {
    ContactCategoryV1,
    vaccineV1Options,
    yesNoUnknownV1Options,
    InformedByV1,
    MessageTemplateTypeV1,
} from '@dbco/enum';
import { isYes } from '../../formOptions';
import * as contactModalV1 from '../../formSchemaV1/modals/contactModal';
import { formatDate } from '@/utils/date';
import { add } from 'date-fns';
import { StoreType } from '@/store/storeType';
import { userCanEdit } from '@/utils/interfaceState';
import type { TaskV2 } from '@dbco/schema/task/taskV2';
import type { TaskV3 } from '@dbco/schema/task/taskV3';
import type { IndexStoreState } from '@/store/index/indexStore';
import type { TaskStoreState } from '@/store/task/taskStore';
import store from '@/store';

export const guidelinesSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => {
    const caseUuid: IndexStoreState['uuid'] = store.getters['index/uuid'];
    const taskUuid: TaskStoreState['uuid'] = store.getters['task/uuid'];

    return generator.slot(
        [
            generator.formChapter(
                [
                    generator.group(
                        [
                            generator
                                .field('general', 'email')
                                .sendEmail(
                                    caseUuid,
                                    taskUuid ?? null,
                                    MessageTemplateTypeV1.VALUE_contactInfection,
                                    'Verstuur adviezen naar contact',
                                    'primary'
                                ),
                        ],
                        'ml-3'
                    ),
                ],
                'Adviezen'
            ),
        ],
        [
            {
                prop: 'general.category',
                values: [null],
                not: true,
            },
        ],
        StoreType.TASK
    );
};

export const vaccinationSchema = (generator: SchemaGenerator<TaskV2 | TaskV3>) => [
    generator.field('vaccination', 'isVaccinated').radioButtonGroup(
        'Dit contact is gevaccineerd',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.field('vaccination', 'vaccineInjections').repeatableGroup(
                'Prik toevoegen',
                [
                    generator.group(
                        [
                            SchemaGenerator.orphanField('injectionDate')
                                .datePicker(
                                    '(Geschatte) datum prik',
                                    '2021-01-01',
                                    formatDate(new Date(), 'yyyy-MM-dd')
                                )
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
                1
            ),
        ]
    ),
];

export const contactConversationButtonToggleGroup = (generator: SchemaGenerator<TaskV2>) =>
    generator.buttonToggleGroup(
        'Contactgesprek',
        'Contactgesprek starten',
        [
            generator.formChapter(contactModalV1.personalDetailsSchema(generator), 'Identificeren van het contact'),
            generator.formChapter(contactModalV1.symptomsSchema(generator), 'COVID-19 status'),
            generator.formChapter(contactModalV1.testSchema(generator)),
            generator.formChapter(contactModalV1.reinfectionSchema(generator), 'Bescherming'),
            generator.formChapter(vaccinationSchema(generator)),
            generator.formChapter(contactModalV1.immunityScheme(generator)),

            generator.slot(
                [
                    generator.formChapter(contactModalV1.jobSchema(generator), 'Opmerkingen en bijzonderheden'),
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
                contactModalV1.givenAdviceSchema(generator, 'row'),
                'Opmerkingen & afgesproken beleid'
            ),
        ],
        ContactConversationDetails,
        ContactConversationSendButton
    );

export const contactModalSchema = <TModel extends TaskV2>(isSource = false) => {
    const generator = new SchemaGenerator<TModel>();

    const chapters: Children<TModel> = [
        generator.formChapter(contactModalV1.aboutSchema(generator, isSource), 'Over het contact'),

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
        generator.formChapter(contactModalV1.shareIndexWithContact(generator)),

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
        guidelinesSchema(generator),
    ];

    if (userCanEdit()) {
        chapters.push(generator.formChapter(contactModalV1.messageBoxSchema(generator), 'Verzonden berichten'));
    }

    return generator.toConfig(chapters);
};
