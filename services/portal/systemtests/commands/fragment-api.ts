import type { CovidCaseUnionDTO } from '@dbco/schema/unions';

type FragmentTypes = 'cases' | 'tasks';
type CaseFragmentKeys =
    | 'index'
    | 'contact'
    | 'alternateContact'
    | 'alternativeLanguage'
    | 'deceased'
    | 'symptoms'
    | 'test'
    | 'vaccination'
    | 'hospital'
    | 'underlyingSuffering'
    | 'pregnancy'
    | 'recentBirth'
    | 'medication'
    | 'generalPractitioner'
    | 'alternateResidency'
    | 'housemates'
    | 'riskLocation'
    | 'job'
    | 'eduDaycare'
    | 'principalContextualSettings'
    | 'abroad'
    | 'contacts'
    | 'groupTransport'
    | 'sourceEnvironments'
    | 'communication'
    | 'immunity'
    | 'extensiveContactTracing';

type CaseFragmentRequest = Pick<CovidCaseUnionDTO, CaseFragmentKeys>;

export function updateFragment(type: 'cases', uuid: string, data: Partial<CaseFragmentRequest>): Cypress.Chainable;

// eslint-disable-next-line no-warning-comments
// TODO: when using tasks fragment; please add task fragment types
export function updateFragment(type: 'tasks', uuid: string, data: Record<any, any>): Cypress.Chainable;
export function updateFragment(type: FragmentTypes, uuid: string, data: Record<any, any>) {
    return cy.authenticatedAPIRequest({ url: `/api/${type}/${uuid}/fragments`, method: 'PUT', body: data });
}
