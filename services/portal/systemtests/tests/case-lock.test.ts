export const visitTaskByUser = (createdCallToAction) => {
    cy.loginAs('gebruiker');

    cy.visit('/taken');

    cy.findByText(/meer taken laden/i).should('be.visible');

    cy.findByLabelText('loading-button').should('not.exist');

    cy.findByRole('row', {
        name: new RegExp(createdCallToAction.subject),
    }).click();

    cy.findByText(/Taak geselecteerd/).should('be.visible');
};

describe('Case lock', () => {
    it('should lock case when someone is editing the case and remove when user deletes lock', () => {
        cy.loginAs('gebruikerWerkverdeler', { followRedirect: false });

        cy.createCaseApi().then((data) => {
            cy.visit(`/editcase/${data.uuid}`);

            cy.createCallToAction(data.uuid).then((createdCallToAction) => {
                visitTaskByUser(createdCallToAction);
                cy.findByText(/Taak oppakken/).click();

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                })
                    .findByText(/Opgepakt door jou/i)
                    .should('be.visible');

                cy.findByRole('link', {
                    name: /Naar dossier/i,
                })
                    .should('be.visible')
                    .invoke('attr', 'target', '_self')
                    .click();

                cy.findByText(
                    /je kunt dit dossier nu niet bewerken\. demo ggd1 gebruiker & werkverdeler, demo ggd1 is in het dossier bezig\./i
                ).should('be.visible');

                cy.loginAs('gebruikerWerkverdeler');
                cy.removeCaseLock(data.uuid);

                visitTaskByUser(createdCallToAction);

                cy.findByRole('link', {
                    name: /Naar dossier/i,
                })
                    .should('be.visible')
                    .invoke('attr', 'target', '_self')
                    .click();

                cy.findByText(
                    /je kunt dit dossier nu niet bewerken\. demo ggd1 gebruiker & werkverdeler, demo ggd1 is in het dossier bezig\./i
                ).should('not.exist');
            });
        });
    });

    it('should remove case lock', () => {
        cy.loginAs('gebruikerWerkverdeler', { followRedirect: false });

        cy.createCaseApi().then((data) => {
            cy.visit(`/editcase/${data.uuid}`);

            cy.createCallToAction(data.uuid).then((createdCallToAction) => {
                visitTaskByUser(createdCallToAction);
                cy.findByText(/Taak oppakken/).click();

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                })
                    .findByText(/Opgepakt door jou/i)
                    .should('be.visible');

                cy.findByRole('link', {
                    name: /Naar dossier/i,
                })
                    .should('be.visible')
                    .invoke('attr', 'target', '_self')
                    .click();

                // set time clock before tick is called, initialized before page is loaded
                const now = new Date();
                cy.clock(now);

                cy.findByText(
                    /je kunt dit dossier nu niet bewerken\. demo ggd1 gebruiker & werkverdeler, demo ggd1 is in het dossier bezig\./i
                ).should('be.visible');

                cy.intercept('GET', '/api/case/**/lock', {
                    statusCode: 204,
                    body: {},
                }).as('getCaseLock');

                // fast forward in time for lock to be removed
                cy.tick(31000);

                cy.findByText(/dit dossier is weer beschikbaar/i).should('be.visible');

                cy.findByRole('button', {
                    name: /dossier bewerken/i,
                }).click();

                cy.findByText(/dit dossier is weer beschikbaar/i).should('not.exist');
            });
        });
    });
});
