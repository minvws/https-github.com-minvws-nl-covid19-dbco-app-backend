import faker from '@/utils/faker-decorator';

describe('Case contact identification', () => {
    it('should be able to add new contact on a case and show the correct info in contact modal', () => {
        const contactFirstname = faker.person.firstName();
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) => {
            cy.visit(`/editcase/${uuid}#contacts`);
        });

        cy.findByPlaceholderText('Voeg contact toe').type(contactFirstname).blur();
        // input element is not visible because of vue-formulate way of styling radio/btn groups; so force true is needed
        cy.findByLabelText('GGD').check({ force: true });

        cy.findByLabelText('Laden').should('be.visible');
        cy.findByRole('button', { name: 'Openen' }).click();

        cy.findByRole('heading', { name: new RegExp(contactFirstname, 'i') });
        // Should be uncommented when bug is fixed. https://ggdcontact.atlassian.net/browse/CACO-209
        // cy.findByRole('button', { name: /Contactgesprek starten/i }).click();
        cy.findByText(/Identificeren van het contact/i)
            .parent()
            .findByText(contactFirstname)
            .should('exist');
    });
});
