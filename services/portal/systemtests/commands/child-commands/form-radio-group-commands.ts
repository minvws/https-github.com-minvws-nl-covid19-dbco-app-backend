import { yesNoUnknownV1Options } from '@dbco/enum';
const _ = Cypress._;
/**
 * @summary
 * Command to select radio group answers in the DBCO form (Yes/No/Unknown)
 * @description
 * Because we encounter a really weird race condition with Form radio elements and Cypress where click/checkbox
 * commands are not effecting the form data. The click event and form 'change' event are somehow only when Cypress clicks in
 * the wrong order, which is causing the application change detection to not picking up the change correctly (submit is earlier then actual data change picked up).
 * A work arround is to click a second time and fire a form change DOM event manually; to not cluther our tests with this command is created.
 * This is only for selecting answers in Form Radio Groups; so the 'YES/No/Unknown' question; for everything else just use Cypress click/checkbox commands.
 */
export const selectGroupOption = (subject: JQuery<HTMLElement>, option: keyof typeof yesNoUnknownV1Options) => {
    const differentOptionText = _.head(
        _.values<(typeof yesNoUnknownV1Options)[keyof typeof yesNoUnknownV1Options]>(
            _.omit(yesNoUnknownV1Options, [option])
        )
    );

    cy.wrap(subject).findAllByLabelText(differentOptionText).first().check({ force: true });
    cy.wrap(subject).find('form').first().trigger('change');

    // As the above mutation is sometimes not persisted (we are not sure exactly when this happens) we cannot
    // wait for last update or use an intercept.
    cy.wait(1000);

    cy.wrap(subject).findAllByLabelText(yesNoUnknownV1Options[option]).first().check({ force: true });
    cy.wrap(subject).find('form').first().trigger('change');
};
