import faker from '../../utils/faker-decorator';

describe('Planner - Case lists', () => {
    it('should be able to create a cast list', () => {
        const newListName = faker.lorem.word();
        cy.loginAs('werkverdeler');

        cy.findByRole('button', { name: /Lijsten/i }).click();
        cy.findByText(/Meer lijsten laden.../).should('not.be.visible');
        cy.findByRole('button', { name: /Nieuwe lijst maken/i }).click();

        cy.findByLabelText(/Naam lijst/i).type(newListName);
        cy.findByRole('button', { name: /Lijst maken/i }).click();

        cy.findByRole('button', { name: /Lijsten/i }).click();

        cy.scrollAllInfinitePages(
            () => cy.findByRole('menu').findByRole('table').invoke('attr', 'aria-rowcount').then(parseInt),
            () =>
                cy
                    .findAllByRole('row')
                    .its('length')
                    .then((count) => count - 2),
            () => cy.findByRole('menu')
        );
        cy.findByRole('cell', { name: new RegExp(`${newListName}`) })
            .scrollIntoView()
            .should('be.visible')
            .parent()
            .click();

        cy.url().should('include', 'planner/');
        cy.findByRole('heading', {
            name: new RegExp(`/ ${newListName}`),
        });

        // Should show an empty list (list has just been created)
        cy.findByText(/Er zijn geen cases/i).should('be.visible');

        // CLEANUP Remove case list to prevent clutter
        cy.url().then((url: string) => {
            const uuid = new URL(url).pathname.split('/').slice(-1)[0];
            cy.removeCaseList(uuid);
        });
    });
});
