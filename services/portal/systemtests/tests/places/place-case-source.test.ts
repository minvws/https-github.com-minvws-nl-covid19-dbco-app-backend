import type { CreatedCase } from '@/commands/case-api';
import { givenCasePayload } from '@/commands/case-api';
import { toDateString } from '@/utils/date-format';
import faker from '@/utils/faker-decorator';
import { YesNoUnknownV1 } from '@dbco/enum';
import { sub, add } from 'date-fns';
import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';

describe('Places - detail page - source tab', () => {
    let caseData: CaseCreateUpdate;
    let dateOfTest: Date;
    let contextName: string;

    it('should add a case, fill source and add context to this case', { tags: '@skip-regresssion' }, () => {
        contextName = faker.lorem.words();
        dateOfTest = faker.date.recent(faker.number.int({ min: 1, max: 30 }));
        caseData = givenCasePayload({
            test: {
                dateOfTest: toDateString(dateOfTest),
            },
        });

        cy.loginAs('gebruikerClusterspecialist', { followRedirect: false });
        cy.createCaseApi(caseData).as('createdCase');
        cy.createContext({ label: contextName }).as('createdContext');
        cy.get<CreatedCase>('@createdCase').then(({ uuid }) => {
            cy.updateFragment('cases', uuid, {
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    symptoms: [],
                },
                test: {
                    ...caseData.test,
                    dateOfSymptomOnset: toDateString(dateOfTest),
                },
            });
            cy.visit(`/editcase/${uuid}#source`);
        });

        cy.findByPlaceholderText('Omschrijving').type(contextName).blur();
        waitForLoadingOfContextTableRow();

        const datesPresentInContext = [
            sub(dateOfTest, {
                days: 4, // source period
            }),
            sub(dateOfTest, {
                days: 2, // overlap date
            }),
            dateOfTest, // infection period
            add(dateOfTest, {
                days: 1, // infection period
            }),
        ];

        // select all moments
        cy.findAllByPlaceholderText('Kies datum(s)').first().click();
        cy.wrap(datesPresentInContext).each((date: Date) => {
            return cy
                .findAllByPlaceholderText('Kies datum(s)')
                .first()
                .parents('.datepicker')
                .findByTestId('calendar')
                .findByText(date.getDate())
                .click({ force: true })
                .wait(500);
        });

        // close calendar
        cy.get('body').click({ force: true });
        waitForLoadingOfContextTableRow();

        cy.findAllByTestId('relationship-select')
            .first()
            .select(faker.number.int({ min: 1, max: 8 }))
            .blur();

        // save select data
        // "then()" breaks the query chain. This is needed to save only the value (see https://github.com/cypress-io/cypress/issues/25173)
        cy.findAllByTestId('relationship-select')
            .first()
            .then((el) => el.val())
            .as('selectedRelationContext');
        waitForLoadingOfContextTableRow();

        cy.findByRole('row', {
            name: new RegExp(contextName),
        })
            .findByRole('checkbox')
            .check({ force: true })
            .should('be.checked');

        cy.findByTestId('context-edit-button').click();
        cy.findByRole('button', {
            name: /context koppelen/i,
        }).click();

        // connect existing context
        cy.findByRole('link', { name: new RegExp(contextName) }).click();
        cy.findByLabelText(/Ik kan niet precies aangeven waar binnen deze context de index was/).check({ force: true });
        cy.findByRole('button', {
            name: /opslaan/i,
        }).click();

        cy.findByRole('button', {
            name: /terug naar index/i,
        }).click();

        // checkbox should still be checked
        cy.findByRole('row', {
            name: new RegExp(contextName),
        })
            .findByRole('checkbox')
            .should('be.checked');
    });
});

function waitForLoadingOfContextTableRow() {
    cy.findByLabelText('context-loading-button').should('be.visible');
    cy.findByLabelText('context-loading-button').should('not.exist');
}
