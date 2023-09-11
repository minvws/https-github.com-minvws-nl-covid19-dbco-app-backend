import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import { givenCasePayload } from '@/commands/case-api';
import faker from '@/utils/faker-decorator';
import escapeStringRegexp from 'escape-string-regexp';

describe('Case - about', () => {
    let initialCasePayload: CaseCreateUpdate = undefined;
    beforeEach(() => {
        initialCasePayload = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });

        cy.createCaseApi(initialCasePayload).then((data) => {
            cy.visit(`/editcase/${data.uuid}`);
        });
    });

    it('should persist contact details', () => {
        const { index } = initialCasePayload;

        const phoneNumber = '06 20915615';
        const email = faker.internet.email();

        cy.get('form').contains('form', 'Contactgegevens').as('contactDefailsForm');

        cy.get('@contactDefailsForm')
            .findByLabelText(/Mobiel telefoonnummer/i)
            .clear()
            .type(phoneNumber);

        cy.waitForLastUpdate();

        cy.get('@contactDefailsForm')
            .findByLabelText(/E\-mailadres/i)
            .clear()
            .type(email);

        cy.waitForLastUpdate();

        cy.reload();

        cy.get('form').contains('form', 'Contactgegevens').as('contactDefailsForm');

        cy.get('@contactDefailsForm')
            .findByLabelText(/Mobiel telefoonnummer/i)
            .should('have.value', phoneNumber);

        cy.get('@contactDefailsForm')
            .findByLabelText(/E\-mailadres/i)
            .should('have.value', email);

        cy.findByRole('button', { name: /Verstuur e-mail bij geen gehoor/i }).click();

        cy.findByRole('dialog', {
            name: /Belpoging van uw GGD/i,
        }).as('sent-email-dialog');

        cy.get('@sent-email-dialog').should('exist');
        cy.get('@sent-email-dialog')
            .findByRole('button', { name: new RegExp(escapeStringRegexp(email), 'i') })
            .should('be.visible');
        cy.get('@sent-email-dialog')
            .findByText(new RegExp(escapeStringRegexp(`Beste ${index.firstname} ${index.lastname}`), 'i'))
            .should('be.visible');
    });

    it('should persist about conversion - BCO-gesprek is met een ander persoon dan de index', () => {
        cy.get('form').contains('form', 'Over het gesprek').as('conversationForm');

        cy.get('@conversationForm')
            .findByRole('group', { name: /ander persoon dan de index/i })
            .as('conversationFormRadioGroup');

        cy.get('@conversationFormRadioGroup').findAllByRole('radio').filter(':checked').should('not.exist');

        cy.get('@conversationFormRadioGroup')
            .contains('label', /onbekend/i)
            .click();

        // We experience a weird race condition in the UI (only in CYPRESS....) where the 'change' event is dispatched before the form state is updated;
        // that's why we dispatch another change event to 'trigger' the fragment submit
        cy.get('@conversationFormRadioGroup').trigger('change');

        // LastUpdate does not show up for some reason
        // But not waiting for the update will cause issues with the sync, that will reset our selected value
        cy.waitForLastUpdate();

        cy.get('@conversationFormRadioGroup')
            .findAllByRole('radio')
            .filter(':checked')
            .invoke('attr', 'id')
            .then((id) => cy.get('@conversationFormRadioGroup').find(`label[for="${id}"]`))
            .invoke('text')
            .should('match', /onbekend/i);

        cy.get('@conversationForm')
            .findByLabelText(/Voornaam vertegenwoordiger/i)
            .should('not.be.visible');

        cy.get('@conversationFormRadioGroup').contains('label', /ja/i).click();

        cy.get('@conversationForm')
            .findByLabelText(/Voornaam vertegenwoordiger/i)
            .should('be.visible');
    });
});
