import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';
import * as caseSummaryTabV1 from '../../formSchemaV1/tabs/caseSummaryTab';

export const caseSummaryTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    const chapters = [
        generator.formChapter(caseSummaryTabV1.contactsSummarySchema(generator), 'Contacten'),
        generator.formChapter(caseSummaryTabV1.contextsSummarySchema(generator), 'Contexten'),
        generator.formChapter(caseSummaryTabV1.hpZoneExportSchema(generator), 'Samenvatting'),
    ];

    chapters.push(generator.formChapter(caseSummaryTabV1.messageBoxSchema(generator), 'Verzonden berichten'));

    return generator.toConfig(chapters);
};
