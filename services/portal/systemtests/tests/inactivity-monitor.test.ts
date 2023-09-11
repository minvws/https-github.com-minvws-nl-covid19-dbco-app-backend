import { add } from 'date-fns';

describe('Inactivity Timer', () => {
    it('Timer is set after login and removed after logout', () => {
        cy.getCookie('InactivityTimerExpiryDate').should('not.exist');

        cy.waitForLoadingCallsToFinnish(() => {
            cy.loginAs('gebruiker');
            cy.getCookie('InactivityTimerExpiryDate').should('exist');
        });

        cy.logout();

        cy.getCookie('InactivityTimerExpiryDate').should('not.exist');
    });

    it('Refreshing the page will refresh the timer', () => {
        cy.loginAs('gebruiker');

        cy.getCookie('InactivityTimerExpiryDate').then((oldCookie) => {
            cy.reload();
            cy.getCookie('InactivityTimerExpiryDate').should((newCookie) => {
                expect(Date.parse(newCookie.value)).to.be.greaterThan(Date.parse(oldCookie.value));
            });
        });
    });

    it('Activity on the page will refresh the timer', () => {
        cy.loginAs('gebruiker');

        cy.getCookie('InactivityTimerExpiryDate').then((oldCookie) => {
            cy.intercept('/api/session-refresh').as('refresh');

            cy.findByRole('heading').click();
            // give it a little bit more time before timing out because request is delayed by 5 seconds
            cy.wait('@refresh', { timeout: 10 * 1000 });

            cy.getCookie('InactivityTimerExpiryDate').should((newCookie) => {
                expect(Date.parse(newCookie.value)).to.be.greaterThan(Date.parse(oldCookie.value));
            });
        });
    });

    it('Show modal when timer almost up', { tags: '@skip-regresssion' }, () => {
        cy.loginAs('gebruiker');

        const expireDate = add(new Date(), { minutes: 2 });

        cy.setCookie('InactivityTimerExpiryDate', expireDate.toISOString());

        cy.findByText(/sessie is bijna verlopen/).should('be.visible');
    });

    it('Logout when timer up', { tags: '@skip-regresssion' }, () => {
        cy.loginAs('gebruiker');
        cy.url().should('include', '/cases');

        const expireDate = add(new Date(), { seconds: 5 }); // give page some time to load before it expires
        cy.setCookie('InactivityTimerExpiryDate', expireDate.toISOString());

        cy.url().should('include', '/login');
    });
});
