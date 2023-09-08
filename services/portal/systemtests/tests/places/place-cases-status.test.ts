import { givenCasePayload } from '@/commands/case-api';
import { toDateString } from '@/utils/date-format';
import faker from '@/utils/faker-decorator';
import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import type { CreatedCase } from '@/commands/case-api';
import { YesNoUnknownV1 } from '@dbco/enum';
import type { CreatedContext } from '@/commands';

describe('Place cases table - status icons', () => {
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
            cy.visit(`/editcase/${uuid}`);
        });
    });
    it('should show a tombstone icon for a deceased case', () => {
        cy.findByRole('group', { name: /Index is overleden/i }).selectGroupOption(YesNoUnknownV1.VALUE_yes);
        cy.waitForLastUpdate();

        connectCaseToContext();

        // open context
        cy.get<CreatedContext>('@createdContext').then(({ uuid }) => {
            cy.visit(`/editplace/${uuid}`);
        });

        cy.findByText(/Er zijn verder geen indexen met een EZD in de 28 dagen voor/i).should('be.visible');
        cy.findByRole('tooltip', { name: 'Index is overleden' }).should('exist');
    });

    it('should show a exclamation bubble icon for a case that is concerned about the context', () => {
        connectCaseToContext();

        cy.findByRole('group', { name: /Er bestaan zorgen over de situatie op locatie/i }).selectGroupOption(
            YesNoUnknownV1.VALUE_yes
        );
        cy.waitForLastUpdate();

        cy.get<CreatedContext>('@createdContext').then(({ uuid }) => {
            cy.visit(`/editplace/${uuid}`);
        });

        cy.findByText(/Er zijn verder geen indexen met een EZD in de 28 dagen voor/i).should('be.visible');
        cy.findByRole('tooltip', { name: 'Index maakt zich zorgen over de situatie op locatie' }).should('exist');
    });

    function connectCaseToContext() {
        cy.findByRole('tab', { name: /Contacten/i }).click();
        cy.findByPlaceholderText('Omschrijving').type(contextName).blur();
        cy.findByLabelText('context-loading-button').should('be.visible');
        cy.findByLabelText('context-loading-button').should('not.exist');
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
