import type { ContextV1 } from '@dbco/schema/context/contextV1';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';
import type { CovidCaseV5 } from '@dbco/schema/covidCase/covidCaseV5';
import type { CovidCaseV6 } from '@dbco/schema/covidCase/covidCaseV6';
import type { CovidCaseV7 } from '@dbco/schema/covidCase/covidCaseV7';
import type { CovidCaseV8 } from '@dbco/schema/covidCase/covidCaseV8';
import type { TaskV3 } from '@dbco/schema/task/taskV3';
import type { TaskV4 } from '@dbco/schema/task/taskV4';
import type { TaskV5 } from '@dbco/schema/task/taskV5';
import type { TaskV6 } from '@dbco/schema/task/taskV6';
import type { TaskV7 } from '@dbco/schema/task/taskV7';
import { sidebarSchema as sidebarSchemaV1 } from '../formSchemaV1/caseSidebar';
import { sourceContactModalSchema as sourceContactModalSchemaV1 } from '../formSchemaV1/modals/contactModal';
import {
    contextModalSchema as contextModalSchemaV1,
    contextModalSidebarSchema as contextModalSidebarSchemaV1,
} from '../formSchemaV1/modals/contextModal';
import contextRulesV1 from '../formSchemaV1/rules/context';
import taskRulesV1 from '../formSchemaV1/rules/task';
import { aboutIndexTabSchema as aboutIndexTabSchemaV1 } from '../formSchemaV1/tabs/aboutIndexTab';
import { historyTabSchema as historyTabSchemaV1 } from '../formSchemaV1/tabs/historyTab';
import { residenceWorkTabSchema as residenceWorkTabSchemaV1 } from '../formSchemaV1/tabs/residenceWorkTab';
import { sourceTracingTabSchema as sourceTracingTabSchemaV1 } from '../formSchemaV1/tabs/sourceTracingTab';
import indexRulesV2 from '../formSchemaV2/rules/index';
import type { FormField } from '../formTypes';
import type { Schema } from '../schemaType';
import {
    contactModalContagiousSidebarSchema,
    contactModalSchema,
    contactModalSourceSidebarSchema,
} from './modals/contactModal';
import { caseAdvicesTabSchema as caseAdvicesTabSchemaV3 } from './tabs/caseAdvicesTab';
import { caseSummaryTabSchema as caseSummaryTabSchemaV3 } from './tabs/caseSummaryTab';
import { contactTracingTabSchema as contactTracingTabSchemaV3 } from './tabs/contactTracingTab';
import { medicalTabSchema } from './tabs/medicalTab';

export type AllowedVersions = {
    index: CovidCaseV3 | CovidCaseV4 | CovidCaseV5 | CovidCaseV6 | CovidCaseV7 | CovidCaseV8;
    context: ContextV1;
    task: TaskV3 | TaskV4 | TaskV5 | TaskV6 | TaskV7;
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
            return contactModalSchema<TTask>();
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

const schema: Schema<CovidCaseV3, ContextV1, TaskV3> = {
    version: 3,
    tabs: [
        {
            type: 'tab',
            id: 'about',
            title: 'Over de index',
            schema: () => aboutIndexTabSchemaV1<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'medical',
            title: 'Medisch',
            schema: () => medicalTabSchema<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'residence-work',
            title: 'Woon & werk',
            schema: () => residenceWorkTabSchemaV1<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'source',
            title: 'Bronnen',
            schema: () => sourceTracingTabSchemaV1<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'contacts',
            title: 'Contacten',
            schema: () => contactTracingTabSchemaV3<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'advices',
            title: 'Adviezen',
            schema: () => caseAdvicesTabSchemaV3<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'finish',
            title: 'Afronden',
            schema: () => caseSummaryTabSchemaV3<CovidCaseV3>(),
        },
        {
            type: 'tab',
            id: 'history',
            title: 'Geschiedenis',
            schema: () => historyTabSchemaV1<CovidCaseV3>(),
        },
    ],
    sidebar: () => sidebarSchemaV1<CovidCaseV3>(),
    rules: {
        index: indexRulesV2,
        context: contextRulesV1,
        task: taskRulesV1,
    },
};

export default schema;
