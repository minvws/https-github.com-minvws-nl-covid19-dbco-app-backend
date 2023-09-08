import faker from '../utils/faker-decorator';

describe('Case', () => {
    it('should create a case', () => {
        cy.loginAs('gebruiker');
        cy.visit('/cases');
        cy.findByRole('button', { name: '＋ Case aanmaken' }).click();

        const hpZoneNummer = faker.case.hpZoneNumber();
        const voornaam = faker.person.firstName();
        const initials = voornaam.charAt(0);
        const achternaam = faker.person.lastName();
        const dateOfBirth = '01-01-1950';
        cy.findByLabelText(/Achternaam/i).type(achternaam);
        cy.findByLabelText(/Voornaam/i).type(voornaam);
        cy.findByLabelText(/Initialen/i).type(initials);
        cy.findByLabelText(/Geboortedatum/i).type(dateOfBirth);
        cy.findByLabelText(/Mobiel telefoonnummer/i).type('0612345678');
        cy.findByLabelText(/HPZone-nummer/i).type(hpZoneNummer);

        // When submitting, we should be redirected to the case detail page
        cy.findByRole('button', { name: 'Doorgaan' }).click();
        cy.findByRole('heading', { level: 2 }).within(() => cy.findByText(`${voornaam} (${initials}) ${achternaam}`));

        // When navigating back to the overview, we should see a new entry
        cy.visit(`/cases`);
        cy.findByText(`${voornaam} (${initials}) ${achternaam}`).should('exist');
    });
});
