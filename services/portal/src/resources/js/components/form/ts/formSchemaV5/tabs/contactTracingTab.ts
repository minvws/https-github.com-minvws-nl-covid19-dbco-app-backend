import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as contactTracingTabV1 from '../../formSchemaV1/tabs/contactTracingTab';
import * as contactTracingTabV2 from '../../formSchemaV2/tabs/contactTracingTab';
import * as contactTracingTabV4 from '../../formSchemaV4/tabs/contactTracingTab';

export const contactTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        contactTracingTabV1.formInfoSchema(generator),
        generator.formChapter(contactTracingTabV2.contactsSchema(generator), 'Contacten binnen besmettelijke periode'),
        generator.formChapter(contactTracingTabV4.contactsCountSchema(generator)),
        generator.formChapter(
            contactTracingTabV1.contactsContagiousPeriodSchema(generator),
            'Contexten binnen besmettelijke periode'
        ),
        generator.formChapter(
            contactTracingTabV1.groupTransportSchema(generator),
            'Vliegreis of groepsvervoer met gereserveerde stoelen'
        ),
    ]);
};
