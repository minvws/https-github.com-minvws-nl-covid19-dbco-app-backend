describe('Planner - case actions', () => {
    it('should assign a case to a user', () => {
        cy.loginAs('werkverdeler', { followRedirect: false });

        // create initial case using API
        cy.createCaseApi().then(({ general: { reference } }) => {
            cy.visit('/planner');

            // Sort cases to make sure the new one is visible
            cy.findByText(/laatste wijziging/i).click();

            // assign case to user
            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            })
                .findByText(/Toewijzen/i)
                .click();
            cy.findByRole('menu').findByText('Demo GGD1 Gebruiker').click();

            // assigned case should be visible in 'assigned cases' tab
            cy.findByRole('tab', { name: /Toegewezen/i }).click();

            // Sort cases to make sure the new one is visible
            cy.findByText(/laatste wijziging/i).click();

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).should('be.visible');

            // expect normal user to have case assigned
            cy.loginAs('gebruiker');
            cy.visit('/cases');

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).should('be.visible');
        });
    });
});
