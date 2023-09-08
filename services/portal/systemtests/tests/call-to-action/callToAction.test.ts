import faker from '../../utils/faker-decorator';
import { toDateString } from '../../utils/date-format';

describe(`CallToAction`, () => {
    it(`User can access tasks by clicking on "Taken" navigation item`, () => {
        cy.loginAs('gebruiker');

        cy.findByRole('link', { name: /taken/i }).click();
        cy.url().should('include', 'taken');

        cy.findAllByText(/Taken/).should('be.visible');
        cy.findByRole('table').should('be.visible');
    });

    it(`User sees an empty sidebar on initial tasks view`, () => {
        cy.loginAs('gebruiker');

        cy.findByRole('link', { name: /taken/i }).click();

        cy.findByRole('complementary').should('be.visible');
        cy.findByText(/Geen taak geselecteerd/).should('be.visible');
        cy.findByText(/Klik op een taak om deze te bekijken en op te pakken/).should('be.visible');
    });

    it(`User can add and view a task by clicking on it in the table and return a the task`, () => {
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) => {
            cy.createCallToAction(uuid).then((createdCallToAction) => {
                cy.visit('/taken');

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                }).click();

                cy.findByText(/Taak geselecteerd/).should('be.visible');
                cy.findByText(/Taak oppakken/).click();

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                })
                    .findByText(/Opgepakt door jou/i)
                    .should('be.visible');

                cy.findByText(/Taak teruggeven/).click();

                const answer = faker.lorem.words(20);
                cy.findByRole('textbox').should('be.visible').type(answer);

                cy.findByRole('button', {
                    name: /teruggeven/i,
                }).click();

                cy.findByText(
                    /de taakbeschrijving en de aanmaker van de taak kunnen pas worden weergegeven, nadat je de taak hebt opgepakt\./i
                ).should('be.visible');

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                })
                    .findByText(/Nog niet opgepakt/i)
                    .should('be.visible');
            });
        });
    });

    it(`User can view details of a task`, () => {
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) => {
            cy.createCallToAction(uuid).then((createdCallToAction) => {
                cy.visit('/taken');

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                }).click();

                cy.findByText(/Taak geselecteerd/).should('be.visible');

                cy.findByText(/Taak oppakken/).click();
                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                })
                    .findByText(/Opgepakt door jou/i)
                    .should('be.visible');

                cy.findByRole('heading', {
                    name: /geschiedenis/i,
                }).should('be.visible');

                cy.findByRole('heading', {
                    name: /taak opgepakt door demo ggd1 gebruiker/i,
                }).should('be.visible');

                cy.findByRole('link', {
                    name: /Naar dossier/i,
                }).should('be.visible');

                cy.findByRole('button', {
                    name: /Taak teruggeven/i,
                }).should('be.visible');

                cy.findByRole('button', {
                    name: /Taak afronden/i,
                }).should('be.visible');
            });
        });
    });

    it(`User gets a warning when task is not assignable`, () => {
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi().then(({ uuid }) => {
            cy.createCallToAction(uuid).then((createdCallToAction) => {
                cy.visit('/taken');

                cy.findByRole('row', {
                    name: new RegExp(createdCallToAction.subject),
                }).click();

                cy.findByText(/Taak geselecteerd/).should('be.visible');

                cy.intercept('POST', '/api/call-to-actions/**/pickup', {
                    statusCode: 404,
                    body: {
                        backendError: {
                            message: 'Taak is niet (meer) beschikbaar',
                            status: 404,
                        },
                    },
                }).as('getCallToActionError');

                cy.findByText(/Taak oppakken/).click();

                cy.wait('@getCallToActionError');

                cy.findByText(/taak is niet \(meer\) beschikbaar/i).should('be.visible');
                cy.findByText(
                    /je kunt daarom de taak niet openen\. ververs de pagina om te zien welke taken nog openstaan\./i
                ).should('be.visible');

                cy.findByRole('button', { name: /ok/i }).click();
            });
        });
    });

    it(`User should see an icon that the date of the task has expired`, () => {
        cy.loginAs('gebruiker');
        const subject = faker.lorem.words(4);
        const date = faker.date.past({ years: faker.number.int({ min: 10, max: 60 }) });

        cy.intercept('GET', '/api/call-to-actions?**', {
            body: {
                from: 1,
                to: 20,
                total: 19,
                currentPage: 1,
                lastPage: 1,
                data: [
                    {
                        uuid: faker.string.uuid(),
                        subject: subject,
                        description: faker.lorem.words(15),
                        createdAt: toDateString(date),
                        expiresAt: toDateString(date),
                    },
                ],
            },
        }).as('getCallToActionCall');

        cy.visit('/taken');

        cy.wait('@getCallToActionCall');

        cy.findByRole('row', {
            name: new RegExp(subject),
        })
            .should('be.visible')
            .findByRole('cell', {
                name: /expired\-date\-icon/i,
            });
    });
});
