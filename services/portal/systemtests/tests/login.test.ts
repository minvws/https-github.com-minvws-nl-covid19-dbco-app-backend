describe('Login/Logout flow', () => {
    it('Reset consent and Login', () => {
        cy.clearCookies();
        cy.visit('/login');

        cy.findByRole('button', {
            name: /reset consent/i,
        }).click();

        cy.waitForLoadingCallsToFinnish(() => {
            // Login
            cy.findByRole('link', { name: 'Demo GGD1: Gebruiker' }).click();

            // Accept privacy Statement
            cy.findByLabelText(/Ik heb bovenstaande gelezen/i).check({
                force: true,
            });
            cy.findByRole('button').click();
        });

        // Logout
        cy.findByTestId('profile-dropdown').click();
        cy.findByText('Logout').click();
        cy.url().should('include', 'login');
    });
});
