import type { CreatedCase } from '@/commands';
import { givenCasePayload } from '@/commands';
import { toDateString } from '@/utils/date-format';
import faker from '@/utils/faker-decorator';

describe('Planner - Case overview filters', () => {
    beforeEach(() => {
        cy.loginAs('werkverdeler');
    });
    it('should be able to apply age filter', () => {
        const age = 9;
        const dateOfBirth = faker.date.birthdate({ min: age, max: age, mode: 'age' });
        cy.createCaseApi(
            givenCasePayload({ index: { ...givenCasePayload().index, dateOfBirth: toDateString(dateOfBirth) } })
        ).as('createdCase');

        cy.findByRole('button', {
            name: /alle leeftijden/i,
        }).click();

        cy.findByRole('spinbutton', {
            name: /tot en met/i,
        })
            .clear()
            .type(age.toString());

        cy.findByRole('button', {
            name: /filteren/i,
        }).click();

        cy.get<CreatedCase>('@createdCase').then((createdCase) => {
            cy.findByRole('row', {
                name: new RegExp(createdCase.general.reference),
            })
                .should('exist')
                .findByRole('cell', {
                    name: age.toString(),
                })
                .should('be.visible');
        });

        cy.findByRole('button', {
            name: new RegExp(`0 t/m ${age} jaar`),
        }).click();

        cy.findByRole('button', {
            name: /wissen/i,
        }).click();

        cy.findByRole('button', {
            name: /alle leeftijden/i,
        }).click();

        cy.findByRole('spinbutton', {
            name: /van/i,
        })
            .clear()
            .type(`${age + 1}`);

        cy.findByRole('button', {
            name: /filteren/i,
        }).click();

        cy.findByText(/meer cases laden/i).should('be.visible');

        cy.findByText(/meer cases laden/i).should('not.be.visible');

        cy.get<CreatedCase>('@createdCase').then((createdCase) => {
            cy.findByRole('row', {
                name: new RegExp(createdCase.general.reference),
            }).should('not.exist');
        });
    });

    it("should not show the age filter in 'uitbesteed' tab", () => {
        cy.findByRole('tab', {
            name: /uitbesteed/i,
        }).click();

        cy.findByRole('button', {
            name: /alle leeftijden/i,
        }).should('not.exist');
    });
});
