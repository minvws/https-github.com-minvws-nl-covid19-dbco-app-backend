export const scrollAllInfinitePages = (
    findTotal: () => Cypress.Chainable<number>,
    findCurrent: () => Cypress.Chainable<number>,
    findScrollContainer: () => Cypress.Chainable<any>
) => {
    return findTotal().then((total) => {
        findCurrent().then((length) => {
            if (length < total) {
                return findScrollContainer()
                    .scrollTo('bottom')
                    .get('.infinite-loader')
                    .should('be.visible')
                    .get('.infinite-loader')
                    .should('not.be.visible')
                    .then(() => {
                        cy.log('infinite scroll to next page');
                        return cy.scrollAllInfinitePages(findTotal, findCurrent, findScrollContainer);
                    });
            } else {
                cy.log('End of infinite scroll');
                return;
            }
        });
    });
};
