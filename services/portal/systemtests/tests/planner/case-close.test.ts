import faker from '../../utils/faker-decorator';

describe('Planner - Case close', { tags: '@skip-regresssion' }, () => {
    beforeEach(() => {
        cy.loginAs('werkverdeler', { followRedirect: false });
    });
    it('should be able to close as a planner', () => {
        // create initial case using API
        cy.createCaseApi().then(({ general: { reference } }) => {
            cy.visit('/planner');

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            })
                .findByRole('button', { name: /Acties/i })
                .click();

            // close case
            cy.findByRole('menu').findByText('Sluiten').click();
            cy.findByLabelText(/Toelichting/i).type(faker.lorem.sentence());
            cy.findByRole('button', { name: /Case\(s\) sluiten/i }).click();

            // row should be removed from the list in view
            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).should('not.exist');

            // row should be added to the 'recently closed' list
            cy.findByRole('tab', { name: /Recent gesloten/i }).click();

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).should('exist');
        });
    });
});
