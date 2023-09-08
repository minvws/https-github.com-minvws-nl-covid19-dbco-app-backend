import CovidCaseHistoryTabs from '@/components/caseEditor/CovidCaseHistoryTabs/CovidCaseHistoryTabs.vue';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import type { AllowedVersions } from '..';
import type { IndexStoreState } from '@/store/index/indexStore';

export const CovidCaseHistorySchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const indexUuid: IndexStoreState['uuid'] = store.getters['index/uuid'];
    const osirisNumber: number | undefined = store.getters['index/osirisNumber'];

    return [generator.component(CovidCaseHistoryTabs, { caseUuid: indexUuid, caseOsirisNumber: osirisNumber })];
};

export const historyTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([generator.formChapter(CovidCaseHistorySchema(generator), '', false)]);
};
