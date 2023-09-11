describe(`Error Notifications`, () => {
    const errorDialog = () =>
        cy.findByRole('dialog', {
            name: /Er gaat iets mis/,
        });

    it(`User is notified when an error occurs`, () => {
        cy.intercept(
            { method: 'GET', url: '/api/**', times: 1 },
            {
                statusCode: 500,
            }
        );
        cy.loginAs('gebruiker');

        errorDialog().should('be.visible');
        errorDialog().findByRole('button', { name: /close/i }).click();
        errorDialog().should('not.exist');
    });

    const permissionAlertDialog = () =>
        cy.findByRole('dialog', {
            name: /Je hebt geen toegang/,
        });

    it(`User is notified when he doesn't have permission`, () => {
        cy.intercept(
            { method: 'GET', url: '/api/**', times: 1 },
            {
                statusCode: 403,
            }
        );

        cy.loginAs('gebruiker');

        permissionAlertDialog().should('be.visible');
        permissionAlertDialog().findByRole('button', { name: /close/i }).click();
        permissionAlertDialog().should('not.exist');
    });

    const offlineAlert = () => cy.findByTestId('offline-error-alert');
    const offlineDialog = () =>
        cy.findByRole('dialog', {
            name: /Verbindingsproblemen/,
        });

    it(`User is notified when the internet connection is lost`, () => {
        cy.loginAs('gebruiker');

        cy.window().then((window) => {
            window.dispatchEvent(new Event('offline'));
        });

        offlineAlert().should('be.visible');
        offlineAlert().findByRole('button', { name: 'Meer informatie' }).click();

        offlineDialog().should('be.visible');
        offlineDialog().findByRole('button', { name: /close/i }).click();
        offlineDialog().should('not.exist');
    });
});
