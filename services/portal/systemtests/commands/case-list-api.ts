export const removeCaseList = (uuid: string): void => {
    cy.authenticatedAPIRequest({
        method: 'DELETE',
        url: `/api/caselists/${uuid}`,
    });
};
