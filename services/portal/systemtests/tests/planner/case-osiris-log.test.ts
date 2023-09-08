describe('Planner - Case osiris log', () => {
    it('should see Osiris log in case details', { tags: '@skip-regresssion' }, () => {
        cy.loginAs('werkverdeler', { followRedirect: false });
        cy.createCaseApi().then(({ general: { reference } }) => {
            cy.visit('/planner');

            // open initial case in detail and see Osiris log
            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).click();

            cy.findByRole('dialog')
                .findByRole('tab', {
                    name: 'Osiris',
                })
                .click();

            // Find Osiris log
            cy.findByRole('heading', {
                name: /Osiris melding succesvol/i,
            });
        });
    });
});
