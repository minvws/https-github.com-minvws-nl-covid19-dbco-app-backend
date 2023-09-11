const users = {
    gebruiker: '00000000-0000-0000-0000-000000000001',
    gebruikerWerkverdeler: '00000000-0000-0000-0000-000000000002',
    werkverdeler: '00000000-0000-0000-0000-000000000004',
    gespreksCoach: '00000000-0000-0000-0000-000000000012',
    supervisor: '00000000-0000-0000-0000-000000000013',
    medischeSupervisie: '00000000-0000-0000-0000-000000000011',
    werkverdelerLS1: '10000000-0000-0000-0000-000000000002',
    contextbeheerder: '00000000-0000-0000-0000-100000000001',
    clusterspecialist: '00000000-0000-0000-0000-200000000001',
    gebruikerClusterspecialist: '00000000-0000-0000-0000-200000000002',
    callcenterBasis: '00000000-0000-0000-0000-000000000014',
    admin: '00000000-0000-0000-0000-000000000020',
};

export type User = keyof typeof users;

/**
 * Login as a specific user by visiting the 'mocked login page' and accepting the consent dialog (if displayed).
 * This will help setup the context of the test to be logged in a a specific role no need to do the login steps.
 *
 * @example cy.loginAs('werkverdeler')
 */
export const loginAs = (user: User, { followRedirect }: { followRedirect?: boolean } = {}) => {
    cy.clearCookies();
    cy.wrap(user).as('loggedInUser');
    const loginPage = `/auth/stub?uuid=${users[user]}`;

    if (followRedirect === undefined || followRedirect === true) {
        cy.visitAndAcceptConsent(loginPage);
        return;
    }

    cy.request({
        url: loginPage,
        followRedirect: false,
    });
    cy.visitAndAcceptConsent('/');
};

export const logout = () => {
    return cy.visit('logout').then(() => {
        cy.url().should('include', '/login');
    });
};

/**
 * Do a request to the API with the current cookies/session of the logged-in user.
 * This can be helpful to setup the API with some 'mocked' data. Without need to visit/fill-in the 'create case form' multiple times we can use this to create multiple cases using the API.
 */
export const authenticatedAPIRequest = <T = Record<string, unknown>>(
    options: Partial<Cypress.RequestOptions>
): Cypress.Chainable<Cypress.Response<T>> => {
    return cy
        .get('[name="csrf-token"]')
        .invoke('attr', 'content')
        .then((token) => {
            return cy.request({
                ...options,
                headers: {
                    ...options.headers,
                    ['Content-Type']: 'application/json',
                    ['X-CSRF-TOKEN']: token,
                },
            });
        });
};

/**
 * There is a bug in the logout flow: https://ggdcontact.atlassian.net/browse/FAB-21
 * By waiting for the backend calls to be finished, there is no overlap between api and logout calls
 */
export const waitForLoadingCallsToFinnish = (callback: CallableFunction) => {
    cy.intercept('/api/caselabels').as('labels');
    cy.intercept('/api/cases/mine*').as('cases');

    callback();

    cy.wait('@labels', { timeout: 10 * 1000 });
    cy.wait('@cases', { timeout: 10 * 1000 });
};

export const visitAndAcceptConsent = (url: string) => {
    cy.visit(url).then(({ location }) => {
        if (location.pathname.endsWith('/consent')) {
            cy.acceptPortalConsent().then(() => {
                cy.visit(url);
            });
        }
    });
};

export const acceptPortalConsent = () =>
    cy
        .get('[name="_token"]')
        .invoke('val')
        .then((token) =>
            cy.request({
                method: 'POST',
                followRedirect: true,
                url: '/consent',
                form: true,
                body: { _token: token, consent: true },
            })
        );
