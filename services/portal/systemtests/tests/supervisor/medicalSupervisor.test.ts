import { ExpertQuestionTypeV1 } from '@dbco/enum';
import faker from '../../utils/faker-decorator';

describe(`Medical supervisor expert question list`, () => {
    it(`Medical supervisor cannot access index page with expert questions for Medical supervisor`, () => {
        cy.loginAs('medischeSupervisie');
        cy.visit('/gesprekscoach', { failOnStatusCode: false });

        cy.findAllByText(/Geen toegang/).should('be.visible');
    });

    it(`Medical supervisor can access index page with expert questions for Medical supervisor`, () => {
        cy.loginAs('medischeSupervisie');
        cy.visit('/medische-supervisie');

        cy.findAllByText(/Hulpvragen medische supervisie/).should('be.visible');
    });

    it(`Medical supervisor is redirected to the index page with expert questions for Medical supervisor after logging in`, () => {
        cy.loginAs('medischeSupervisie');
        cy.url().should('include', 'medische-supervisie');

        cy.findByRole('link', { name: /medische supervisie/i }).should('be.visible');

        cy.findAllByText(/Hulpvragen medische supervisie/).should('be.visible');

        // is table with cases visible
        cy.findByRole('table').should('be.visible');
    });

    it(`Medical supervisor should be able to search for cases`, () => {
        const question = faker.lorem.words(20);
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then((createdCase) => {
            cy.addQuestionToCase(createdCase.uuid, { type: ExpertQuestionTypeV1.VALUE_medical_supervision, question });
            cy.loginAs('medischeSupervisie');

            // zoek naar 'created case' reference nr
            cy.get('#medical-supervisor-search-input').should('be.visible').type(createdCase.general.reference);
        });

        cy.findByRole('button', {
            name: /zoek casenummer/i,
        }).click();

        cy.findByText(question).should('be.visible');
    });

    it(`User should be able to ask a question`, () => {
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) => cy.visit(`/editcase/${uuid}`));

        // open hulp vragen dialog
        cy.findByRole('button', {
            name: /hulp vragen/i,
        }).click();

        // fill in 'question' form
        cy.findByRole('combobox', {
            name: /wie wil je hulp vragen\?/i,
        }).select('Medische Supervisie');
        cy.findByLabelText(/Telefoonnummer waarop je bereikbaar bent/i).type('0612345678');
        cy.findByLabelText(/onderwerp/i).type(faker.lorem.words(5));
        cy.findByLabelText(/toelichting/i).type(faker.lorem.words(20));

        // submit question
        cy.findByRole('button', {
            name: /verstuur vraag/i,
        }).click();

        cy.findByRole('dialog', { name: /stel je vraag/i }).should('not.exist');
        cy.findByText('Vraag is verstuurd aan Medische Supervisie').should('be.visible');
    });

    it(`Medical supervisor should be able to pick up question related to the case and answer it`, () => {
        const subject = faker.lorem.words(5);
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) =>
            cy.addQuestionToCase(uuid, { type: ExpertQuestionTypeV1.VALUE_medical_supervision, subject })
        );
        cy.loginAs('medischeSupervisie');
        cy.visit('/');

        cy.findByRole('row', {
            name: new RegExp(subject),
        }).click();

        // pick up the case question
        cy.findByRole('button', {
            name: /vraag oppakken/i,
        }).click();

        // case information is visible when picking up case question
        cy.findByRole('row', {
            name: new RegExp(subject),
        })
            .findByText(/Opgepakt door jou/i)
            .should('be.visible');

        cy.findByRole('button', {
            name: /Terugzetten/i,
        }).should('be.visible');

        cy.findByRole('link', {
            name: /Naar dossier/i,
        }).should('be.visible');

        const answer = faker.lorem.words(20);
        cy.get('#expert-form-textarea').type(answer);
        cy.findByRole('button', {
            name: /Beantwoorden/i,
        }).click();

        cy.findByText(answer).should('be.visible');

        cy.findByRole('row', {
            name: new RegExp(subject),
        })
            .findByText(/Beantwoord door jou/i)
            .should('be.visible');
    });

    it(`Medical supervisor should be able to sort questions`, () => {
        const subject = faker.lorem.words(5);
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) =>
            cy.addQuestionToCase(uuid, { type: ExpertQuestionTypeV1.VALUE_medical_supervision, subject })
        );

        cy.loginAs('medischeSupervisie');
        cy.url().should('include', '/medische-supervisie');

        cy.findByText(/meer cases laden/i).should('not.be.visible');

        // is table with cases visible
        cy.findAllByRole('row').should('have.length.gte', 2);

        cy.findByRole('columnheader', {
            name: /tijdstip/i,
        }).click();
    });
});
