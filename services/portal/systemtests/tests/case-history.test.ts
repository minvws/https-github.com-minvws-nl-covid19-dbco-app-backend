import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import faker from '../utils/faker-decorator';
import type { ExpertQuestionRequest, ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';

it('should show history item for user assignment', () => {
    cy.loginAs('gebruiker', { followRedirect: false });

    cy.createCaseApi().then(({ uuid }) => cy.visit(`/editcase/${uuid}#history`));

    cy.findByRole('heading', { name: /Toewijzing/ });
    cy.findByText(/toewijzing van de case is veranderd door medewerker: naar/i);
});

it('should show history item for created call to actions / tasks', () => {
    cy.loginAs('gebruiker', { followRedirect: false });

    cy.createCaseApi().then(({ uuid }) => {
        cy.createCallToAction(uuid).as('createdCallToAction');
        cy.visit(`/editcase/${uuid}#history`);
    });

    cy.get<CallToActionResponse>('@createdCallToAction').then(({ subject, description }) => {
        cy.findByRole('heading', { name: subject });
        cy.findByText(description);
    });
});

it('should show history item for expert questions', () => {
    cy.loginAs('gebruiker', { followRedirect: false });

    const question: Partial<ExpertQuestionRequest> = {
        subject: faker.lorem.sentence(),
        question: faker.lorem.paragraph(),
    };

    cy.createCaseApi().then(({ uuid }) => {
        cy.addQuestionToCase(uuid, question).its('body').as('createdQuestion');
        cy.visit(`/editcase/${uuid}#history`);
    });

    cy.get<ExpertQuestionResponse>('@createdQuestion').then(({ subject, question }) => {
        cy.findByRole('heading', { name: subject });
        cy.findByText(question);
    });
});
