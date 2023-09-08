import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as contactTracingTabV1 from '../../formSchemaV1/tabs/contactTracingTab';
import * as contactTracingTabV2 from '../../formSchemaV2/tabs/contactTracingTab';

export const contactTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    const chapters: Children<TModel> = [contactTracingTabV1.formInfoSchema(generator)];

    chapters.push(generator.formChapter(contactTracingTabV2.extensiveContactTracingSchema(generator)));

    return generator.toConfig([
        ...chapters,
        generator.formChapter(contactTracingTabV2.contactsSchema(generator), 'Contacten binnen besmettelijke periode'),
        generator.formChapter(
            contactTracingTabV1.contactsContagiousPeriodSchema(generator),
            'Contexten binnen besmettelijke periode'
        ),
        generator.formChapter(
            contactTracingTabV1.groupTransportSchema(generator),
            'Vliegreis of groepsvervoer met gereserveerde stoelen'
        ),
        generator.formChapter(contactTracingTabV1.pairCaseSchema(generator), 'GGD Contact'),
    ]);
};
