import ContactSummaryTable from '@/components/caseEditor/ContactSummaryTable/ContactSummaryTable.vue';
import ContextSummaryTable from '@/components/caseEditor/ContextSummaryTable/ContextSummaryTable.vue';
import HpZoneExport from '@/components/caseEditor/HpZoneExport/HpZoneExport.vue';
import MessageBox from '@/components/caseEditor/MessageBox/MessageBox.vue';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { userCanEdit } from '@/utils/interfaceState';
import type { AllowedVersions } from '..';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { IndexStoreState } from '@/store/index/indexStore';

export const contactsSummarySchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const caseUuid = store.getters['index/uuid'];
    return [
        generator.component(ContactSummaryTable, {
            caseUuid,
        }),
    ];
};

export const contextsSummarySchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const caseUuid: IndexStoreState['uuid'] = store.getters['index/uuid'];

    return [
        generator.component(ContextSummaryTable, {
            caseUuid,
        }),
    ];
};

export const hpZoneExportSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const caseUuid: IndexStoreState['uuid'] = store.getters['index/uuid'];

    return [
        generator.component(HpZoneExport, {
            caseUuid,
        }),
    ];
};

export const messageBoxSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.component(MessageBox),
];

export const caseSummaryTabSchema = <TModel extends CovidCaseV1 | CovidCaseV2>() => {
    const generator = new SchemaGenerator<TModel>();

    const chapters = [
        generator.formChapter(contactsSummarySchema(generator), 'Contacten'),
        generator.formChapter(contextsSummarySchema(generator), 'Contexten'),
        generator.formChapter(hpZoneExportSchema(generator), 'Samenvatting'),
    ];

    if (userCanEdit()) {
        chapters.push(generator.formChapter(messageBoxSchema(generator), 'Verzonden berichten'));
    }

    return generator.toConfig(chapters);
};
