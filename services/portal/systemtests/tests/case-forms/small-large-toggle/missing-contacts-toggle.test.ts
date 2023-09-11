import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import { givenCasePayload } from '@/commands/case-api';
import { YesNoUnknownV1 } from '@/../../shared/packages/dbco-enum/output/ts';

describe('Case - toggle missing contacts', () => {
    let initialCasePayload: CaseCreateUpdate = undefined;
    beforeEach(() => {
        initialCasePayload = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });

        cy.createCaseApi(initialCasePayload).then((data) => {
            cy.visit(`/editcase/${data.uuid}#contacts`);
        });
    });

    it('should hide missing contacts question when contact tracing is set to "standard" and the question is unanswered', () => {
        cy.findByText('Ontbraken er nog contacten (cat. 1/2/3) in de tabel?').should('be.visible');

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText('Ontbraken er nog contacten (cat. 1/2/3) in de tabel?').should('not.exist');
    });

    it('should NOT hide missing contacts question when contact tracing is set to "standard" and the question is answered', () => {
        cy.findByRole('group', { name: 'Ontbraken er nog contacten (cat. 1/2/3) in de tabel?' }).selectGroupOption(
            YesNoUnknownV1.VALUE_yes
        );

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText('Ontbraken er nog contacten (cat. 1/2/3) in de tabel?').should('be.visible');
    });
});
