import type { ContextV1 } from '@dbco/schema/context/contextV1';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';
import type { CovidCaseV5 } from '@dbco/schema/covidCase/covidCaseV5';
import type { CovidCaseV6 } from '@dbco/schema/covidCase/covidCaseV6';
import type { CovidCaseV7 } from '@dbco/schema/covidCase/covidCaseV7';
import type { CovidCaseV8 } from '@dbco/schema/covidCase/covidCaseV8';
import type { TaskV1 } from '@dbco/schema/task/taskV1';
import type { TaskV2 } from '@dbco/schema/task/taskV2';
import type { TaskV3 } from '@dbco/schema/task/taskV3';
import type { TaskV4 } from '@dbco/schema/task/taskV4';
import type { TaskV5 } from '@dbco/schema/task/taskV5';
import type { TaskV6 } from '@dbco/schema/task/taskV6';
import type { TaskV7 } from '@dbco/schema/task/taskV7';
import type { FormField } from '../formTypes';
import type { Schema } from '../schemaType';
import { sidebarSchema } from './caseSidebar';
import {
    contactModalContagiousSidebarSchema,
    contactModalSchema,
    contactModalSourceSidebarSchema,
    sourceContactModalSchema,
} from './modals/contactModal';
import { contextModalSchema, contextModalSidebarSchema } from './modals/contextModal';
import contextRules from './rules/context';
import indexRules from './rules/index';
import taskRules from './rules/task';
import { aboutIndexTabSchema } from './tabs/aboutIndexTab';
import { caseAdvicesTabSchema } from './tabs/caseAdvicesTab';
import { caseSummaryTabSchema } from './tabs/caseSummaryTab';
import { contactTracingTabSchema } from './tabs/contactTracingTab';
import { historyTabSchema } from './tabs/historyTab';
import { medicalTabSchema } from './tabs/medicalTab';
import { residenceWorkTabSchema } from './tabs/residenceWorkTab';
import { sourceTracingTabSchema } from './tabs/sourceTracingTab';

export type AllowedVersions = {
    index:
        | CovidCaseV1
        | CovidCaseV2
        | CovidCaseV3
        | CovidCaseV4
        | CovidCaseV5
        | CovidCaseV6
        | CovidCaseV7
        | CovidCaseV8;
    context: ContextV1;
    task: TaskV1 | TaskV2 | TaskV3 | TaskV4 | TaskV5 | TaskV6 | TaskV7;
};

export enum SectionTypes {
    contactModalContagious = 'contact-modal-contagious',
    contactModalSource = 'contact-modal-source',
    contextModal = 'context-modal',
    contextModalSidebar = 'context-modal-sidebar',
    contactModalContagiousSidebar = 'contact-modal-contagious-sidebar',
    contactModalSourceSidebar = 'contact-modal-source-sidebar',
}

export const getSchema = <TIndex extends CovidCaseV1, TContext extends ContextV1, TTask extends TaskV1>(
    path: string
): FormField[] | null => {
    switch (path) {
        // Modals
        case SectionTypes.contactModalContagious:
            return contactModalSchema<TTask>();
        case SectionTypes.contactModalSource:
            return sourceContactModalSchema<TTask>();
        case SectionTypes.contextModal:
            return contextModalSchema<TContext>();

        // Sidebars
        case SectionTypes.contactModalContagiousSidebar:
            return contactModalContagiousSidebarSchema<TIndex>();
        case SectionTypes.contactModalSourceSidebar:
            return contactModalSourceSidebarSchema<TIndex>();
        case SectionTypes.contextModalSidebar:
            return contextModalSidebarSchema<TIndex>();
    }

    return null;
};

const schema: Schema<CovidCaseV1, ContextV1, TaskV1> = {
    version: 1,
    tabs: [
        {
            type: 'tab',
            id: 'about',
            title: 'Over de index',
            schema: () => aboutIndexTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'medical',
            title: 'Medisch',
            schema: () => medicalTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'residence-work',
            title: 'Woon & werk',
            schema: () => residenceWorkTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'source',
            title: 'Bronnen',
            schema: () => sourceTracingTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'contacts',
            title: 'Contacten',
            schema: () => contactTracingTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'advices',
            title: 'Adviezen',
            schema: () => caseAdvicesTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'finish',
            title: 'Afronden',
            schema: () => caseSummaryTabSchema<CovidCaseV1>(),
        },
        {
            type: 'tab',
            id: 'history',
            title: 'Geschiedenis',
            schema: () => historyTabSchema<CovidCaseV1>(),
        },
    ],
    sidebar: () => sidebarSchema<CovidCaseV1>(),
    rules: {
        index: indexRules,
        context: contextRules,
        task: taskRules,
    },
};

export default schema;
