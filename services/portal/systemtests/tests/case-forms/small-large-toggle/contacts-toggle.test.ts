import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import { givenCasePayload } from '@/commands/case-api';
import faker from '@/utils/faker-decorator';

describe('Case - toggle contacts within infectious period', () => {
    let initialCasePayload: CaseCreateUpdate = undefined;
    beforeEach(() => {
        initialCasePayload = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });

        cy.createCaseApi(initialCasePayload).then((data) => {
            cy.visit(`/editcase/${data.uuid}#contacts`);
        });
    });

    it('should hide contacts table when contact tracing is set to "standard" and the table is empty', () => {
        cy.findByText('Contacten binnen besmettelijke periode').should('be.visible');

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText('Contacten binnen besmettelijke periode').should('not.exist');
    });

    it('should NOT hide contacts table when contact tracing is set to "standard" and the table is filled', () => {
        const contactFirstname = faker.person.firstName();

        cy.findByPlaceholderText('Voeg contact toe').type(contactFirstname).blur();
        // input element is not visible because of vue-formulate way of styling radio/btn groups; so force true is needed
        cy.findByLabelText('GGD').check({ force: true });

        cy.waitForLastUpdate();

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText('Contacten binnen besmettelijke periode').should('be.visible');
        cy.findAllByTestId('task-label-input').should('have.length.gte', 1);
    });
});
