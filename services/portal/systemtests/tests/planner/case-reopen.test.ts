import faker from '../../utils/faker-decorator';

describe('Planner - Case reopen', () => {
    it('should be able to reopen and reassign a case', { tags: '@skip-regresssion' }, () => {
        cy.loginAs('werkverdeler', { followRedirect: false });
        cy.createCaseApi().then(({ general: { reference } }) => {
            cy.visit('/planner');

            // close initial case
            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            })
                .findByRole('button', { name: /Acties/i })
                .click();

            cy.findByRole('menu').findByText('Sluiten').click();
            cy.findByLabelText(/Toelichting/i).type(faker.lorem.sentence());
            cy.findByRole('button', { name: /Case\(s\) sluiten/i }).click();
            cy.findByRole('tab', { name: /Recent gesloten/i }).click();

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            })
                .findByRole('button', { name: /Acties/i })
                .click();
            cy.findByRole('menu').findByText('Heropenen').click();
            cy.findByRole('dialog').findByRole('textbox').type(faker.lorem.sentence());
            cy.findByRole('button', { name: 'Toewijzen' }).click();
            cy.findByRole('button', { name: 'Demo GGD1 Gebruiker' }).click();
            cy.findByRole('button', { name: /Case heropenen/i }).click();
            cy.findByRole('dialog', { name: /heropenen/i }).should('not.exist');

            // row should be added to 'assigned' list
            cy.findByRole('tab', { name: /Toegewezen/i }).click();

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).should('exist');

            // row should be removed from 'recently closed' list
            cy.findByRole('tab', { name: /Recent gesloten/i }).click();

            cy.findByRole('row', {
                name: new RegExp(`${reference}`),
            }).should('not.exist');
        });
    });
});
