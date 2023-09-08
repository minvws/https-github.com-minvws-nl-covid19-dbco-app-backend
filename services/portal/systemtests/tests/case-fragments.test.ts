import { YesNoUnknownV1 } from '@dbco/enum';
import { givenCasePayload } from '../commands/case-api';

describe('Case - fragments', () => {
    it('should persist a certain fragment of a case when filling in form', () => {
        const initialCase = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi(initialCase);

        cy.visit('/cases');

        cy.findByRole('link', {
            name: new RegExp(`${initialCase.index.firstname} ${initialCase.index.lastname}`),
        }).click();

        cy.findByRole('group', { name: /ander persoon dan de index/i }).selectGroupOption(YesNoUnknownV1.VALUE_no);
        cy.waitForLastUpdate();

        cy.findByLabelText(/Opmerkingen en bijzonderheden over het BCO-gesprek/i).type('Heeft in restaurant gegeten');
        cy.waitForLastUpdate();

        // check if comment/fragment is persisted after reloading page
        cy.reload();
        cy.findByDisplayValue(/Heeft in restaurant gegeten/i).should('exist');
    });
});
