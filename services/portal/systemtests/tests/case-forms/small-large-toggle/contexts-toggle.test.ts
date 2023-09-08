import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import { givenCasePayload } from '@/commands/case-api';
import faker from '@/utils/faker-decorator';

describe('Case - toggle contexts within infectious period', () => {
    let initialCasePayload: CaseCreateUpdate = undefined;
    beforeEach(() => {
        initialCasePayload = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });

        cy.createCaseApi(initialCasePayload).then((data) => {
            cy.visit(`/editcase/${data.uuid}#contacts`);
        });
    });

    it('should hide contexts table when contact tracing is set to "standard" and the table is empty', () => {
        cy.findByText('Contexten binnen besmettelijke periode').should('be.visible');

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText('Contexten binnen besmettelijke periode').should('not.exist');
    });

    it('should NOT hide contexts table when contact tracing is set to "standard" and the table is filled', () => {
        const contextFirstname = faker.person.firstName();
        cy.findByPlaceholderText('Omschrijving').type(contextFirstname).blur();
        // input element is not visible because of vue-formulate way of styling radio/btn groups; so force true is needed
        cy.findByLabelText('GGD').check({ force: true });

        cy.waitForLastUpdate();

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText('Contexten binnen besmettelijke periode').should('be.visible');
        cy.findAllByTestId('label-input').should('have.length.gte', 1);
    });
});
