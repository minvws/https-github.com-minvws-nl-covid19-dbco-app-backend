import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { StoreType } from '@/store/storeType';
import { ContactCategoryV1, InformedByV1 } from '@dbco/enum';
import type { AllowedVersions } from '..';
import * as contactModalV1 from '../../formSchemaV1/modals/contactModal';
import * as contactModalV2 from '../../formSchemaV2/modals/contactModal';
import * as contactModalV3 from '../../formSchemaV3/modals/contactModal';
import * as contactModalV5 from '../../formSchemaV5/modals/contactModal';
import { standardBCOInfoSchema } from '../shared/bcoType';

export const caseNumberSchema = (generator: SchemaGenerator<AllowedVersions['task']>) =>
    generator
        .field('test', 'previousInfectionCaseNumber')
        .text('Dossiernummer vorige besmetting')
        .validation(['optional', 'caseNumber']);

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
                contactModalV5.contactConversationButtonToggleGroup(
                    generator,
                    contactModalV3.reinfectionSchema(generator, [caseNumberSchema(generator)])
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

    chapters.push(standardBCOInfoSchema(generator));

    return generator.toConfig(chapters);
};
