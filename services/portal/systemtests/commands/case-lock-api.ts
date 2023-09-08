export const removeCaseLock = (uuid: string) => {
    cy.authenticatedAPIRequest({
        url: `/api/case/${uuid}/lock/remove`,
        method: 'DELETE',
    });
};
