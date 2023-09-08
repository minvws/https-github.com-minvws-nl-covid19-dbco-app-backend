import faker from '@/utils/faker-decorator';

describe('Case contact context creation', () => {
    it('should be able to add new context on a case and show the correct info when opened', () => {
        const contextDescription = faker.lorem.words();
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) => {
            cy.visit(`/editcase/${uuid}#contacts`);
        });

        cy.findByPlaceholderText('Omschrijving').type(contextDescription).blur();
        cy.findByLabelText('context-loading-button').should('be.visible');
        cy.findByLabelText('context-loading-button').should('not.exist');

        cy.findByTestId('context-edit-button').click();

        cy.findByText(contextDescription).should('be.visible');
    });
});
