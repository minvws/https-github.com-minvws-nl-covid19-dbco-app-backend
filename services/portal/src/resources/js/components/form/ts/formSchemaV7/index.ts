import type { ContextV1 } from '@dbco/schema/context/contextV1';
import type { CovidCaseV7 } from '@dbco/schema/covidCase/covidCaseV7';
import type { CovidCaseV8 } from '@dbco/schema/covidCase/covidCaseV8';
import type { TaskV7 } from '@dbco/schema/task/taskV7';
import { sidebarSchema as sidebarSchemaV1 } from '../formSchemaV1/caseSidebar';
import { sourceContactModalSchema as sourceContactModalSchemaV1 } from '../formSchemaV1/modals/contactModal';
import {
    contextModalSchema as contextModalSchemaV1,
    contextModalSidebarSchema as contextModalSidebarSchemaV1,
} from '../formSchemaV1/modals/contextModal';
import contextRulesV1 from '../formSchemaV1/rules/context';
import taskRulesV1 from '../formSchemaV1/rules/task';
import { historyTabSchema as historyTabSchemaV1 } from '../formSchemaV1/tabs/historyTab';
import indexRulesV2 from '../formSchemaV2/rules/index';
import {
    contactModalContagiousSidebarSchema,
    contactModalSourceSidebarSchema,
} from '../formSchemaV3/modals/contactModal';
import { caseSummaryTabSchema as caseSummaryTabSchemaV3 } from '../formSchemaV3/tabs/caseSummaryTab';
import { aboutIndexTabSchema as aboutIndexTabSchemaV5 } from '../formSchemaV5/tabs/aboutIndexTab';
import { contactTracingTabSchema as contactTracingTabSchemaV6 } from '../formSchemaV6/tabs/contactTracingTab';
import { residenceWorkTabSchema as residenceWorkTabSchemaV6 } from '../formSchemaV6/tabs/residenceWorkTab';
import { sourceTracingTabSchema as sourceTracingTabSchemaV6 } from '../formSchemaV6/tabs/sourceTracingTab';
import { contactModalSchema as contactModalSchemaV7 } from '../formSchemaV7/modals/contactModal';
import type { FormField } from '../formTypes';
import type { Schema } from '../schemaType';
import { caseAdvicesTabSchema } from './tabs/caseAdvicesTab';
import { medicalTabSchema } from './tabs/medicalTab';

export type AllowedVersions = {
    index: CovidCaseV7 | CovidCaseV8;
    context: ContextV1;
    task: TaskV7;
};

export enum SectionTypes {
    contactModalContagious = 'contact-modal-contagious',
    contactModalSource = 'contact-modal-source',
    contextModal = 'context-modal',
    contextModalSidebar = 'context-modal-sidebar',
    contactModalContagiousSidebar = 'contact-modal-contagious-sidebar',
    contactModalSourceSidebar = 'contact-modal-source-sidebar',
}

export const getSchema = <
    TIndex extends AllowedVersions['index'],
    TContext extends AllowedVersions['context'],
    TTask extends AllowedVersions['task'],
>(
    path: string
): FormField[] | null => {
    switch (path) {
        // Modals
        case SectionTypes.contactModalContagious:
            return contactModalSchemaV7<TTask>();
        case SectionTypes.contactModalSource:
            return sourceContactModalSchemaV1<TTask>();
        case SectionTypes.contextModal:
            return contextModalSchemaV1<TContext>();

        // Sidebars
        case SectionTypes.contactModalContagiousSidebar:
            return contactModalContagiousSidebarSchema<TIndex>();
        case SectionTypes.contactModalSourceSidebar:
            return contactModalSourceSidebarSchema<TIndex>();
        case SectionTypes.contextModalSidebar:
            return contextModalSidebarSchemaV1<TIndex>();
    }

    return null;
};

const schema: Schema<CovidCaseV7, ContextV1, TaskV7> = {
    version: 7,
    tabs: [
        {
            type: 'tab',
            id: 'about',
            title: 'Over de index',
            schema: () => aboutIndexTabSchemaV5<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'medical',
            title: 'Medisch',
            schema: () => medicalTabSchema<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'residence-work',
            title: 'Woon & werk',
            schema: () => residenceWorkTabSchemaV6<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'source',
            title: 'Bronnen',
            schema: () => sourceTracingTabSchemaV6<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'contacts',
            title: 'Contacten',
            schema: () => contactTracingTabSchemaV6<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'advices',
            title: 'Adviezen',
            schema: () => caseAdvicesTabSchema<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'finish',
            title: 'Afronden',
            schema: () => caseSummaryTabSchemaV3<CovidCaseV7>(),
        },
        {
            type: 'tab',
            id: 'history',
            title: 'Geschiedenis',
            schema: () => historyTabSchemaV1<CovidCaseV7>(),
        },
    ],
    sidebar: () => sidebarSchemaV1<CovidCaseV7>(),
    rules: {
        index: indexRulesV2,
        context: contextRulesV1,
        task: taskRulesV1,
    },
};

export default schema;
