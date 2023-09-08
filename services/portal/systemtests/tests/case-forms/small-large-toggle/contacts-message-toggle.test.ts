import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import { givenCasePayload } from '@/commands/case-api';
import faker from '@/utils/faker-decorator';

describe('Case - toggle contact messages', { tags: '@skip-regresssion' }, () => {
    let initialCasePayload: CaseCreateUpdate = undefined;
    beforeEach(() => {
        initialCasePayload = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });

        cy.createCaseApi(initialCasePayload).then((data) => {
            cy.visit(`/editcase/${data.uuid}#contacts`);
        });
    });

    it('should show correct message when contact tracing is set to "standard" and all content is hidden', () => {
        cy.findByText(
            'De vragen in dit tabblad komen niet terug in een standaard BCO. Kies voor een uitgebreid BCO om deze vragen te tonen.'
        ).should('not.exist');

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText(
            'De vragen in dit tabblad komen niet terug in een standaard BCO. Kies voor een uitgebreid BCO om deze vragen te tonen.'
        ).should('be.visible');
        cy.findByText(
            'Je vult de verkorte vragenlijst in. Mis je een vraag? Kies dan voor het uitvoeren van een uitgebreid BCO.'
        ).should('not.exist');
        cy.findByText('Medische gegevens van index ontbreken. Probeer deze eerst aan te vullen.').should('not.exist');
    });

    it('show correct messages when contact tracing is set to "standard" and there is some content', () => {
        const contactFirstname = faker.person.firstName();

        cy.findByPlaceholderText('Voeg contact toe').type(contactFirstname).blur();
        // input element is not visible because of vue-formulate way of styling radio/btn groups; so force true is needed
        cy.findByLabelText('GGD').check({ force: true });

        cy.waitForLastUpdate();

        cy.findByRole('tab', { name: /over de index/i }).click();

        cy.findByRole('radio', { name: /Standaard/i }).check({ force: true });
        cy.findByRole('tab', { name: /Contacten/i }).click();

        cy.findByText(
            'De vragen in dit tabblad komen niet terug in een standaard BCO. Kies voor een uitgebreid BCO om deze vragen te tonen.'
        ).should('not.exist');
        cy.findByText(
            'Je vult de verkorte vragenlijst in. Mis je een vraag? Kies dan voor het uitvoeren van een uitgebreid BCO.'
        ).should('be.visible');
        cy.findByText('Medische gegevens van index ontbreken. Probeer deze eerst aan te vullen.').should('be.visible');
    });
});
