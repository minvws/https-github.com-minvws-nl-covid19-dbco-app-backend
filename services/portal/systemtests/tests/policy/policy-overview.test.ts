import type { CreatedPolicy } from '@/commands/policy-api';
import { givenPolicyPayload } from '@/commands/policy-api';
import { PolicyVersionStatusV1 } from '@dbco/enum';
import type { PolicyVersion } from '@dbco/portal-api/admin.dto';
import faker from '@/utils/faker-decorator';
import { format } from 'date-fns';

describe('Places - detail page', () => {
    let policyData: PolicyVersion;

    it('should add a new beleidsversie and change status to active', () => {
        const versionName = faker.lorem.words();
        const date = new Date();
        date.setDate(date.getDate());
        const formattedDate = format(date, 'yyyy-MM-dd');

        cy.loginAs('admin');

        cy.findByRole('button', {
            name: /nieuwe beleidsversie/i,
        }).click();

        cy.findByText(/Nieuw beleid/i).should('be.visible');

        cy.findByRole('textbox', {
            name: /versienaam \(verplicht\)/i,
        }).type(versionName);

        cy.findByLabelText(/ingangsdatum \(verplicht\)/i).type(formattedDate.toString());

        cy.findByRole('button', {
            name: /aanmaken/i,
        }).click();

        cy.findByRole('heading', {
            name: new RegExp(versionName),
        }).should('be.visible');

        cy.findByRole('heading', {
            name: /risicoprofielen en richtlijnen/i,
        }).should('be.visible');

        cy.findAllByTestId('last-updated').first().as('lastUpdated');

        cy.get('@lastUpdated')
            .findByText(/Bezig met opslaan/i)
            .should('exist');

        cy.get('@lastUpdated')
            .findByText(/Laatst opgeslagen/i)
            .should('exist');

        cy.findByRole('button', {
            name: /concept/i,
        }).click();

        cy.findByText(
            /wil je klaarzetten voor activatie\? dit beleid wordt automatisch actief op de ingangsdatum/i
        ).should('be.visible');

        cy.findByRole('button', {
            name: /zet klaar/i,
        }).should('be.disabled');

        cy.findByRole('checkbox', {
            name: /een collega heeft dit beleid gecheckt\./i,
        }).check();

        cy.findByRole('button', {
            name: /zet klaar/i,
        }).should('not.be.disabled');

        cy.findByRole('button', {
            name: /zet klaar/i,
        }).click();

        cy.findByText(/actief/i).should('be.visible');

        cy.findByRole('link', {
            name: /terug naar beleidsversies/i,
        }).click();

        cy.findByRole('row', {
            name: new RegExp(versionName),
        })
            .findByRole('cell', {
                name: /actief/i,
            })
            .should('be.visible')
            .click();

        cy.findByRole('textbox', {
            name: /versienaam/i,
        }).should('be.disabled');

        cy.findByRole('row', {
            name: new RegExp('ziekenhuisopname'),
        })
            .findByRole('combobox')
            .should('be.disabled');

        cy.findByTestId('Symptomatisch - Standaard').click();
        cy.findByText(/richtlijnen \//i).should('be.visible');

        // wait for loading indicator to appear and disappear
        cy.findByRole('progressbar').should('exist');
        cy.findByRole('progressbar').should('not.exist');

        cy.findAllByRole('combobox', {
            name: /Type/i,
        })
            .first()
            .should('be.disabled');
    });

    it('should add a new richtlijnen for a beleidsversie and change guideline', () => {
        policyData = givenPolicyPayload({
            startDate: faker.date.soon(),
            status: PolicyVersionStatusV1.VALUE_draft,
        });
        cy.loginAs('admin', { followRedirect: false });
        cy.createPolicyApi(policyData).as('createdPolicy');
        cy.get<CreatedPolicy>('@createdPolicy').then((policy) => {
            cy.visit(`/beheren/beleidsversies/${policy.body.uuid}`);

            cy.findByRole('heading', {
                name: new RegExp(policy.body.name),
            }).should('be.visible');
        });

        cy.findByTestId('Symptomatisch - Standaard').click();
        cy.findByText(/richtlijnen \//i).should('be.visible');

        cy.findAllByRole('combobox', {
            name: /Type/i,
        })
            .first()
            .select('Vast');

        cy.findAllByRole('combobox', {
            name: /Dag/i,
        })
            .first()
            .select('3 dagen voor');

        cy.findAllByRole('combobox', {
            name: /Uitgangsdatum/i,
        })
            .first()
            .select('Testdatum');

        cy.findByRole('button', {
            name: /terug naar beleid/i,
        }).click();
    });
});
