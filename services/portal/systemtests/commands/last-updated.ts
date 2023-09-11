/**
 * @summary
 * Command to wait for all form changes to be persisted
 * @description
 * You do not need to use this command after every form change; only if you depend on form changes to be
 * persisted (for example when asserting on something) or if the state is not persisted correctly.
 * The second case happens for some field types and not others; this is a bit of trial and error for now.
 */
export const waitForLastUpdate = () => {
    cy.findAllByTestId('last-updated').first().as('lastUpdated');

    cy.focused().blur();

    cy.get('@lastUpdated')
        .findByText(/Bezig met opslaan/i)
        .should('exist');

    return cy
        .get('@lastUpdated')
        .findByText(/Laatst opgeslagen/i)
        .should('exist');
};
