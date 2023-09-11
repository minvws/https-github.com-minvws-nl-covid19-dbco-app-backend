import type { CreatedCase } from '@/commands/case-api';
import { givenCasePayload } from '@/commands/case-api';
import { toDateString } from '@/utils/date-format';
import faker from '@/utils/faker-decorator';
import type { ContextRelationshipV1 } from '@dbco/enum';
import { contextRelationshipV1Options, YesNoUnknownV1 } from '@dbco/enum';
import { sub, add } from 'date-fns';
import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import type { CreatedContext } from '@/commands';
import dateFnsFormat from 'date-fns/format';
import { nl } from 'date-fns/locale';

describe('Places - detail page - contacts tab', () => {
    let caseData: CaseCreateUpdate;
    let dateOfTest: Date;
    let contextName: string;

    beforeEach(() => {
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
            cy.visit(`/editcase/${uuid}#contacts`);
        });

        cy.findByPlaceholderText('Omschrijving').type(contextName).blur();
        waitForLoadingOfContextTableRow();
    });
    it('should increase the index count and show relation', { tags: '@skip-regresssion' }, () => {
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

        cy.findByTestId('context-edit-button').click();

        // connect existing context
        connectCaseToContext();

        cy.visit('/places');
        cy.findByText(/meer contexten laden/i).should('not.be.visible');

        // assert context to be connected (counts to be incremented)
        cy.findByRole('row', {
            name: new RegExp(`${contextName}`),
        })
            .findByLabelText('place-index-count')
            .should('be.visible')
            .contains('1');

        cy.findByRole('row', {
            name: new RegExp(contextName),
        }).click();

        cy.get<ContextRelationshipV1>('@selectedRelationContext').then((relationshipOption) => {
            const relationshipOptionTextValue = contextRelationshipV1Options[relationshipOption];
            cy.findByRole('cell', { name: relationshipOptionTextValue }).should('be.visible');
        });
    });
    it('should be able to select moments', () => {
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
        connectCaseToContext();

        // open context
        cy.get<CreatedContext>('@createdContext').then(({ uuid }) => {
            cy.visit(`/editplace/${uuid}`);
        });

        waitForLoadingOfPlaceCasesTable();

        const [sourceDate, overlapDate, ...infectiousDates] = datesPresentInContext;
        cy.findAllByRole('img', { name: 'source dates' }).last().parent().contains(sourceDate.getDate());
        cy.findAllByRole('img', { name: 'overlap date' }).last().parent().contains(overlapDate.getDate());
        cy.findAllByRole('img', { name: 'infectious dates' })
            .last()
            .parent()
            .contains(`${infectiousDates[0].getDate()} - ${infectiousDates[1].getDate()}`);

        cy.findByRole('tab', {
            name: /bezoeken/i,
        }).click();

        cy.findByRole('tabpanel', {
            name: /bezoeken/i,
        })
            .findByText(/binnen de bronperiode/i)
            .should('be.visible');

        const formattedDate = (date) =>
            dateFnsFormat(date, 'd-MM', {
                locale: nl,
            });

        cy.get(`#range-icon-${formattedDate(sourceDate)}`)
            .should('have.attr', 'title')
            .and('match', new RegExp('circle-blue'));
        cy.get(`#range-icon-${formattedDate(overlapDate)}`)
            .should('have.attr', 'title')
            .and('match', new RegExp('range-overlap'));

        cy.get(`#range-icon-${formattedDate(infectiousDates[0])}`)
            .should('have.attr', 'title')
            .and('match', new RegExp('square-red'));

        cy.get(`#range-icon-${formattedDate(infectiousDates[1])}`)
            .should('have.attr', 'title')
            .and('match', new RegExp('square-red'));
    });

    function connectCaseToContext() {
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
    }
});

function waitForLoadingOfContextTableRow() {
    cy.findByLabelText('context-loading-button').should('be.visible');
    cy.findByLabelText('context-loading-button').should('not.exist');
}

function waitForLoadingOfPlaceCasesTable() {
    cy.findByText(/Er zijn verder geen indexen met een EZD in de 28 dagen voor/i).should('be.visible');
}
