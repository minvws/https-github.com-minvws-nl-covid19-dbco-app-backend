import { givenContext } from '@/commands/places-api';
import faker from '@/utils/faker-decorator';

describe('Places - overview page', () => {
    it('should create a new context', () => {
        const context = givenContext();
        cy.loginAs('clusterspecialist');
        cy.findByRole('button', { name: /Context aanmaken/i }).click();

        // fill in all context fields
        cy.findByRole('dialog', { name: /Context aanmaken/i })
            .findByRole('textbox')
            .type(context.label);
        cy.findByRole('dialog', { name: /Context aanmaken/i })
            .findByRole('button', {
                name: /nieuwe aanmaken/i,
            })
            .click();

        cy.findByRole('textbox', { name: /Naam/i }).type(context.label);
        cy.findByRole('textbox', { name: /Postcode/i }).type(context.address.postalCode);
        cy.findByRole('spinbutton', { name: /Huisnummer/i }).type(context.address.houseNumber);
        cy.findByRole('textbox', { name: /Toevoeging/i }).type(context.address.houseNumberSuffix);

        // select context category
        cy.findByRole('button', {
            name: /kies categorie/i,
        }).click();
        cy.findByRole('listbox')
            .findByText(/onderwijs \/ kdv/i)
            .click();
        cy.findByRole('menuitem', {
            name: /hbo of wo/i,
        }).click();

        // save context
        cy.findByRole('dialog', {
            name: /nieuwe context aanmaken/i,
        })
            .findByRole('button', { name: /context aanmaken/i })
            .click();
        cy.findByRole('button', {
            name: /opslaan/i,
        }).click();

        // expect new table row to exists on page
        cy.findByRole('row', {
            name: new RegExp(context.label),
        }).should('exist');
    });
    it('should be able to apply verified filter', { tags: '@skip-regresssion' }, () => {
        const label = faker.lorem.words();
        cy.loginAs('clusterspecialist');
        cy.createContext({ isVerified: true, label });

        cy.findByRole('button', {
            name: /geverifieerd of ongeverifieerd/i,
        }).click();

        // when filtered on 'verified'
        cy.findByRole('menu', { name: /geverifieerd/i }).click();
        cy.findByRole('row', { name: new RegExp(label) }).should('exist');

        // when filtered on 'unverified'
        cy.findByRole('button', {
            name: /geverifieerd/i,
        }).click();
        cy.findByText('ongeverifieerd').click();
        cy.findByText(/Er zijn nog geen locaties./i).should('exist');
    });

    it('should be able to add situation numbers with value and name', () => {
        const context = givenContext();
        cy.loginAs('clusterspecialist');
        cy.createContext(context);

        // reload page to get the newly added context
        cy.reload();

        cy.findByRole('row', {
            name: new RegExp(`${context.label}`),
        })
            .findByLabelText('context-actions')
            .click();

        cy.findByRole('menuitem', {
            name: /context bewerken/i,
        }).click();

        cy.findByRole('button', {
            name: /situatienummer toevoegen/i,
        }).click();

        cy.findByRole('textbox', {
            name: /situatienummer/i,
        }).type(context.situationNumbers[0].value.toString());

        cy.findByRole('textbox', {
            name: /naam situatie/i,
        }).type(context.situationNumbers[0].name);

        // debounce time for saving situationNumbers
        cy.wait(300);

        cy.findByRole('button', {
            name: /opslaan/i,
        }).click();

        cy.findByRole('dialog', {
            name: /context bewerken/i,
        }).should('not.exist');

        cy.findByText(/meer contexten laden/i).should('not.be.visible');

        cy.findByRole('row', {
            name: new RegExp(`${context.label}`),
        })
            .findByRole('button', {
                name: /situation-number-icon/i,
            })
            .should('be.visible');
    });
});
