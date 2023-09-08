import { YesNoUnknownV1 } from '@/../../shared/packages/dbco-enum/output/ts';
import type { CreatedCase } from '@/commands/case-api';
import { givenCasePayload } from '@/commands/case-api';
import { toDateString } from '@/utils/date-format';
import { sub } from 'date-fns';

describe('Case ingestion flow', () => {
    let initialCase: ReturnType<typeof givenCasePayload>;
    beforeEach(() => {
        initialCase = givenCasePayload();
    });

    it('should show message when no cases to pick up', () => {
        cy.loginAs('gebruiker');
        cy.findByRole('button', { name: /oppakken/i }).click();

        cy.findByRole('heading', {
            name: /geen cases voor jou in de wachtrij/i,
        });
    });

    it('should be able to pick up case when there is a case in the queue', () => {
        cy.loginAs('werkverdeler');
        cy.createCaseApi(initialCase)
            .as('createdCase')
            .then(({ general: { reference } }) => {
                cy.reload();
                cy.findByRole('row', {
                    name: new RegExp(reference),
                })
                    .findByRole('button', { name: /Toewijzen/i })
                    .click();

                cy.findByRole('row', {
                    name: new RegExp(reference),
                })
                    .findByRole('menu')
                    .findByText('Wachtrij')
                    .click();
            });

        cy.loginAs('gebruiker');
        cy.findByRole('button', { name: /oppakken/i }).click();

        cy.get<CreatedCase>('@createdCase').then(({ general: { reference } }) => {
            cy.findByRole('heading', { name: new RegExp(reference) });
        });
    });

    it('should be able to complete a case questionaire', () => {
        const firstDaySick = sub(new Date(), { days: 2 });
        const testDate = sub(new Date(), { days: 3 });
        cy.loginAs('gebruiker');
        cy.createCaseApi(initialCase)
            .as('createdCase')
            .then((data) => {
                cy.visit(`/editcase/${data.uuid}`);
            });

        cy.findByRole('group', { name: /ander persoon dan de index/i }).selectGroupOption(YesNoUnknownV1.VALUE_no);

        cy.findByRole('group', {
            name: /liever in een andere taal dan Nederlands/i,
        }).selectGroupOption(YesNoUnknownV1.VALUE_no);

        cy.findByRole('group', { name: /Index is overleden/i }).selectGroupOption(YesNoUnknownV1.VALUE_no);

        cy.findByRole('tab', { name: /Medisch/i }).click();

        cy.findByRole('group', { name: /index klachten/i }).selectGroupOption(YesNoUnknownV1.VALUE_yes);

        cy.findByLabelText(/Neusverkoudheid/i).click({ force: true });
        cy.findByLabelText(/Keelpijn/i).click({ force: true });

        cy.findByLabelText(/Eerste ziektedag/i).type(toDateString(firstDaySick));
        cy.findByLabelText('Testdatum').type(toDateString(testDate));

        cy.findByLabelText('Klachten').click({ force: true });

        // without waiting UI is not reacting the way we expect;
        cy.waitForLastUpdate();

        cy.findByRole('button', { name: /Status updaten/ }).click();
        cy.findByRole('menuitem', { name: /Afronden of teruggeven/ }).click();

        cy.findByRole('button', { name: /Indienen/i }).click();
        cy.findByRole('button', { name: /Ok/i }).click();

        cy.get<CreatedCase>('@createdCase')
            .then(({ general: { reference } }) => cy.findByRole('row', { name: new RegExp(reference) }))
            .should('not.exist');
    });
});
