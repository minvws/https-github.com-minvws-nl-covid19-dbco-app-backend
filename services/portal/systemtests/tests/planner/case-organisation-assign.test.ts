import { givenCasePayload } from '../../commands/case-api';

describe('Planner - case organisation assignment', () => {
    it('should assign a case to a single organisation', () => {
        const initialCase = givenCasePayload();
        cy.loginAs('werkverdeler');

        // create initial case using API
        cy.createCaseApi(initialCase).its('general.reference').as('createdCaseRef');
        cy.visit('/planner');

        // Sort cases to make sure the new one is visible
        cy.findByText(/laatste wijziging/i).click();

        // assign case to user
        cy.get<string>('@createdCaseRef')
            .then((reference) =>
                cy.findByRole('row', {
                    name: new RegExp(`${reference}`),
                })
            )
            .findByText(/Toewijzen/i)
            .click();
        cy.findByRole('menu').findByText('Uitbesteden').click();
        cy.findByRole('menu').findByText('Demo LS1').click();

        // row should be removed
        cy.get<string>('@createdCaseRef')
            .then((reference) =>
                cy.findByRole('row', {
                    name: new RegExp(`${reference}`),
                })
            )
            .should('not.exist');

        // expect normal user to have case assigned
        cy.loginAs('werkverdelerLS1');
        cy.visit('/planner');
        cy.get<string>('@createdCaseRef')
            .then((reference) =>
                cy.findByRole('row', {
                    name: new RegExp(`${reference}`),
                })
            )
            .should('be.visible');

        // expect assigned case not to be reasignable to other organisations
        cy.get<string>('@createdCaseRef')
            .then((reference) =>
                cy.findByRole('row', {
                    name: new RegExp(`${reference}`),
                })
            )
            .findByText(/Toewijzen/i)
            .click();
        cy.findByRole('menu').findByText('Uitbesteden').should('not.exist');
    });
});
