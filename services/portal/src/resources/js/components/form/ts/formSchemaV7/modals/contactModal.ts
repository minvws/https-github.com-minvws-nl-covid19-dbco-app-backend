import ContactConversationDetails from '@/components/caseEditor/ContactConversationDetails/ContactConversationDetails.vue';
import ContactConversationSendButton from '@/components/caseEditor/ContactConversationSendButton/ContactConversationSendButton.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { StoreType } from '@/store/storeType';
import { ContactCategoryV1, InformedByV1 } from '@dbco/enum';
import type { AllowedVersions } from '..';
import * as contactModalV1 from '../../formSchemaV1/modals/contactModal';
import * as contactModalV2 from '../../formSchemaV2/modals/contactModal';
import * as contactModalV3 from '../../formSchemaV3/modals/contactModal';
import * as contactModalV4 from '../../formSchemaV4/modals/contactModal';
import * as contactModalV5 from '../../formSchemaV5/modals/contactModal';
import * as contactModalV6 from '../../formSchemaV6/modals/contactModal';
import { standardBCOInfoSchema as standardBCOInfoSchemaV6 } from '../../formSchemaV6/shared/bcoType';

export const givenAdviceSchema = (generator: SchemaGenerator<AllowedVersions['task']>, className?: string) => {
    return [
        generator.div(
            [
                generator
                    .field('inform', 'otherAdvice')
                    .textArea(
                        'Gegeven adviezen',
                        'Noteer alleen adviezen die je daadwerkelijk gegeven hebt. Denk hierbij ook aan adviezen die je alleen voor deze specifieke situatie hebt gegeven',
                        12
                    ),
            ],
            className
        ),
    ];
};

export const contactConversationButtonToggleGroup = (
    generator: SchemaGenerator<AllowedVersions['task']>,
    reinfectionSchema: Children<AllowedVersions['index']>
) =>
    generator.buttonToggleGroup(
        'Contactgesprek',
        'Contactgesprek starten',
        [
            generator.formChapter(contactModalV5.personalDetailsSchema(generator), 'Identificeren van het contact'),
            generator.formChapter(contactModalV1.symptomsSchema(generator), 'COVID-19 status'),
            generator.formChapter(contactModalV1.testSchema(generator)),
            generator.formChapter(reinfectionSchema, 'Bescherming'),
            generator.formChapter(contactModalV4.vaccinationSchema(generator)),

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
            generator.formChapter(givenAdviceSchema(generator, 'row'), 'Opmerkingen & afgesproken beleid'),
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
        generator.formChapter(contactModalV3.shareIndexWithContact(generator, givenAdviceSchema)),

        generator.slot(
            [
                contactConversationButtonToggleGroup(
                    generator,
                    contactModalV3.reinfectionSchema(generator, [contactModalV6.caseNumberSchema(generator)])
                ),
            ],
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

    chapters.push(standardBCOInfoSchemaV6(generator));

    return generator.toConfig(chapters);
};
