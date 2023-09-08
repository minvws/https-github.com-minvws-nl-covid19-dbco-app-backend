import { givenCasePayload } from '@/commands';
import faker from '@/utils/faker-decorator';
import { format } from 'date-fns';

const formatDateOfBirth = (dateOfBirth: string) => format(new Date(dateOfBirth), 'dd-MM-yyyy');

const searchWithAddress = (data: ReturnType<typeof givenCasePayload>) => {
    cy.findByLabelText(/geboortedatum/i).type(formatDateOfBirth(data.index.dateOfBirth));
    cy.findByLabelText(/laatste 3 cijfers BSN/i).type('111');
    cy.findByLabelText(/postcode/i).type(data.index.address.postalCode);
    cy.findByLabelText(/huisnummer/i).type(data.index.address.houseNumber);
    cy.findByRole('button', { name: /zoeken/i }).click();

    // Without BSN, you need to provide the name
    cy.findByLabelText(/achternaam/i).type(data.index.lastname);
    cy.findByRole('button', { name: /zoeken/i }).click();
};

describe(`Callcenter`, () => {
    it(`Search for a case with an address`, () => {
        const data = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi(data);

        cy.loginAs('callcenterBasis');
        searchWithAddress(data);

        // Should display the name and that BSN does not match
        cy.findByRole('heading', {
            name: /indexdossier/i,
        })
            .parent()
            .siblings('ul')
            .within(() => {
                cy.findByText(data.index.lastname);
                cy.findByText(/Komt niet overeen/i);
            });
    });

    it(`Search for a case with a BSN`, { tags: '@skip-regresssion' }, () => {
        // Directly from LocalBsnRepository
        const bsnData = {
            bsn: '999998286',
            bsnGuid: '1eaf0d45-1124-4799-931d-58f628635079',
            letters: 'AA',
            dateOfBirth: '1950-01-01',
            houseNumber: '01',
            postalCode: '9999XX',
        };
        const data = givenCasePayload({
            index: {
                dateOfBirth: bsnData.dateOfBirth,
                address: {
                    postalCode: bsnData.postalCode,
                    houseNumber: bsnData.houseNumber,
                },
                firstname: faker.person.firstName(),
                lastname: faker.person.lastName(),
            },
            pseudoBsnGuid: bsnData.bsnGuid,
        });

        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi(data);

        cy.loginAs('callcenterBasis');

        cy.findByLabelText(/geboortedatum/i).type(formatDateOfBirth(data.index.dateOfBirth));
        cy.findByLabelText(/laatste 3 cijfers BSN/i).type('286');
        cy.findByLabelText(/postcode/i).type(data.index.address.postalCode);
        cy.findByLabelText(/huisnummer/i).type(data.index.address.houseNumber);
        cy.findByRole('button', { name: /zoeken/i }).click();

        // Should display the last three bsn digits and that BSN does match
        // There could be multiple results, because of the overlapping BSNs on muiltiple testruns
        cy.findAllByRole('heading', {
            name: /indexdossier/i,
        })
            .first()
            .parent()
            .siblings('ul')
            .within(() => {
                cy.findByText('286');
                cy.findByText(/Komt niet overeen/i).should('not.exist');
            });
    });

    it('Add note to case', () => {
        const data = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi(data);

        cy.loginAs('callcenterBasis');
        searchWithAddress(data);

        cy.findByRole('button', { name: /notitie plaatsen/i }).click();
        cy.findByRole('textbox', {
            name: /notitie/i,
        }).type(faker.lorem.sentence());
        cy.findByRole('button', { name: /plaatsen/i }).click();

        cy.findByRole('status').within(() => {
            cy.findByText(/notitie is geplaatst/i);
        });
    });

    it('Add task to case', () => {
        const data = givenCasePayload();
        cy.loginAs('gebruiker', { followRedirect: false });
        cy.createCaseApi(data);

        cy.loginAs('callcenterBasis');
        searchWithAddress(data);

        cy.findByRole('button', { name: /taak aanmaken/i }).click();

        // Use the preselected date. Element is not typable itself
        cy.findByRole('group', {
            name: /uitvoerdatum/i,
        }).click();
        cy.get('body').type('{enter}');

        cy.findByRole('group', {
            name: /onderwerp/i,
        }).within(() => {
            cy.findByRole('textbox').type(faker.word.noun());
        });

        cy.findByRole('group', {
            name: /beschrijving/i,
        }).within(() => {
            cy.findByRole('textbox').type(faker.word.noun());
        });

        cy.findByRole('button', { name: /aanmaken/i }).click();

        cy.findByRole('status').within(() => {
            cy.findByText(/taak aangemaakt/i);
        });
    });
});
