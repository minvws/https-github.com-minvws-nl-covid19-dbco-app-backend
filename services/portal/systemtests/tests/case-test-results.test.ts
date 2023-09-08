import { givenCasePayload } from '@/commands/case-api';
import { toDateString } from '@/utils/date-format';
import faker from '@/utils/faker-decorator';

describe('Case - TestResults', () => {
    it('should be able to add new test results manually', () => {
        const initialCase = givenCasePayload(undefined);
        const testDate = faker.date.past({ years: 1 });
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi(initialCase).then(({ uuid }) => {
            cy.visit(`/editcase/${uuid}`);
        });

        cy.findByRole('tab', {
            name: /medisch/i,
        }).click();
        cy.findByRole('button', { name: /test toevoegen/i }).click();

        // fill in form in dialog
        cy.findByRole('dialog')
            .findByLabelText(/Testdatum/i)
            .type(toDateString(testDate));
        cy.findByRole('dialog')
            .findByLabelText(/Monsternummer/i)
            .type(faker.testResult.monsterNumber());
        cy.findByRole('dialog').findByText('Positief').click();

        cy.findByRole('dialog')
            .findByRole('button', { name: /Toevoegen/i })
            .click();

        // wait for loading indicator to appear and disappear
        cy.findByTestId('loading-spinner').should('exist');
        cy.findByTestId('loading-spinner').should('not.exist');

        // would be nice to have more accessible rows in table so we don't have to query on text
        cy.findByText(/positieve moleculaire diagnostiek/i).should('be.visible');
    });
});
