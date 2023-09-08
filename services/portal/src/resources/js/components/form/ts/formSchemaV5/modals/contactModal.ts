import ContactConversationDetails from '@/components/caseEditor/ContactConversationDetails/ContactConversationDetails.vue';
import ContactConversationSendButton from '@/components/caseEditor/ContactConversationSendButton/ContactConversationSendButton.vue';
import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { StoreType } from '@/store/storeType';
import { ContactCategoryV1, InformedByV1, noBsnOrAddressReasonV1Options } from '@dbco/enum';
import type { AllowedVersions } from '..';
import * as contactModalV1 from '../../formSchemaV1/modals/contactModal';
import * as contactModalV2 from '../../formSchemaV2/modals/contactModal';
import * as contactModalV3 from '../../formSchemaV3/modals/contactModal';
import * as contactModalV4 from '../../formSchemaV4/modals/contactModal';
import { BsnLookupType, FormConditionRule } from '../../formTypes';
import BsnLookup from '@/components/caseEditor/BsnLookup/BsnLookup.vue';
import { userCanEdit } from '@/utils/interfaceState';

export const personalDetailsSchema = (generator: SchemaGenerator<AllowedVersions['task']>) => {
    return [
        generator
            .div(
                [
                    generator.component(BsnLookup, { targetType: BsnLookupType.Task, disabled: !userCanEdit() }, [
                        generator
                            .field('personalDetails', 'hasNoBsnOrAddress')
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
                                    .field('personalDetails', 'bsnNotes')
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
                                    prop: 'personalDetails.hasNoBsnOrAddress',
                                },
                            ],
                            StoreType.TASK
                        ),
                    ]),
                ],
                'w-100'
            )
            // This is needed to open the "BCO-gesprek" / contact conversation toggle group when hasNoBsnOrAddress has a value.
            // This is necessary because buttonToggleGroup is looking recursively at the children fieldnames to see if there is a value.
            .appendConfig({ name: 'personalDetails.hasNoBsnOrAddress' }),
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
            generator.formChapter(personalDetailsSchema(generator), 'Identificeren van het contact'),
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
            [
                contactConversationButtonToggleGroup(
                    generator,
                    contactModalV3.reinfectionSchema(generator, [contactModalV3.infectionHpZoneNumberSchema(generator)])
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

    return generator.toConfig(chapters);
};
