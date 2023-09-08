import { ExpertQuestionTypeV1 } from '@dbco/enum';
import faker from '../../utils/faker-decorator';
const { _ } = Cypress;

describe(`Conversation coach expert question list`, () => {
    it(`Conversation coach cannot access index page with expert questions for Conversation coach`, () => {
        cy.loginAs('gespreksCoach');
        cy.visit('/medische-supervisie', { failOnStatusCode: false });

        cy.findAllByText(/Geen toegang/i).should('be.visible');
    });

    it(`Conversation coach can access index page with expert questions for Conversation coach`, () => {
        cy.loginAs('gespreksCoach');
        cy.visit('/gesprekscoach');

        cy.findAllByText(/Hulpvragen gesprekscoach/i).should('be.visible');
    });

    it(`Conversation coach is redirected to the index page with expert questions for Conversation coach after logging in`, () => {
        cy.loginAs('gespreksCoach');
        cy.url().should('include', '/gesprekscoach');

        cy.findByRole('link', { name: /gesprekscoach/i }).should('be.visible');

        cy.findAllByText(/Hulpvragen gesprekscoach/i).should('be.visible');

        // is table with cases visible
        cy.findByRole('table').should('be.visible');
    });

    it(`Conversation coach should be able to search for cases`, () => {
        const question = faker.lorem.words(20);
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then((createdCase) => {
            cy.addQuestionToCase(createdCase.uuid, { type: ExpertQuestionTypeV1.VALUE_conversation_coach, question });
            cy.loginAs('gespreksCoach');

            // zoek naar 'created case' reference nr in invoerveld
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
        }).select('Gesprekscoach');
        cy.findByLabelText(/Telefoonnummer waarop je bereikbaar bent/i).type('0612345678');
        cy.findByLabelText(/onderwerp/i).type(faker.lorem.words(5));
        cy.findByLabelText(/toelichting/i).type(faker.lorem.words(20));

        // submit question
        cy.findByRole('button', {
            name: /verstuur vraag/i,
        }).click();

        cy.findByRole('dialog', { name: /stel je vraag/i }).should('not.exist');
        cy.findByText('Vraag is verstuurd aan Gesprekscoach').should('be.visible');
    });

    it(`Conversation coach should be able to pick up question related to the case and answer it`, () => {
        const subject = faker.lorem.words(5);
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) =>
            cy.addQuestionToCase(uuid, { type: ExpertQuestionTypeV1.VALUE_conversation_coach, subject })
        );

        cy.loginAs('gespreksCoach');

        cy.findByRole('row', {
            name: new RegExp(`${subject}`),
        }).click();

        // pick up the case question
        cy.findByRole('button', {
            name: /vraag oppakken/i,
        }).click();

        // case information is visible when picking up case question
        cy.findByRole('row', {
            name: new RegExp(`${subject}`),
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

        // Get the textarea in the sidebar
        cy.get('#expert-form-textarea').type(answer);

        cy.findByRole('button', {
            name: /Beantwoorden/i,
        }).click();

        cy.findByText(new RegExp(answer)).should('be.visible');

        cy.findByRole('row', {
            name: new RegExp(`${subject}`),
        })
            .findByText(/Beantwoord door jou/i)
            .should('be.visible');
    });

    it(`Conversation coach should be able to sort questions`, () => {
        const subject = faker.lorem.words(5);

        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) =>
            cy.addQuestionToCase(uuid, { type: ExpertQuestionTypeV1.VALUE_conversation_coach, subject })
        );

        cy.loginAs('gespreksCoach');
        cy.url().should('include', '/gesprekscoach');

        cy.findByText(/meer cases laden/i).should('not.be.visible');

        // is table with cases visible
        cy.findAllByRole('row').should('have.length.gte', 2);

        cy.findByRole('columnheader', {
            name: /tijdstip/i,
        }).click();

        // should display ordered list by time
        cy.findAllByRole('row').should('have.length.gte', 2);
        cy.findAllByRole('row')
            .eq(1)
            .findByRole('cell', {
                name: /een minuut/i,
            })
            .should('be.visible');

        cy.findByRole('columnheader', {
            name: /tijdstip/i,
        }).click();

        cy.findAllByRole('row').should('have.length.gte', 2);

        const toTime = (dates: string[]) => dates.map((time) => new Date(time).getTime());

        cy.get('table')
            .find('time')
            .then((cols) => _.map(cols, 'dateTime'))
            .then((dates) => toTime(dates as unknown as string[]))
            .should((times) => assert.deepEqual(times, _.sortBy(times)));
    });
});
