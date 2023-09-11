// ***********************************************************
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// This ESlint rule is there for Vite, but cypress uses webpack
// eslint-disable-next-line import/no-commonjs, @typescript-eslint/no-var-requires
const registerCypressGrep = require('@cypress/grep');
registerCypressGrep();

// Register testing-library commands
import '@testing-library/cypress/add-commands';

// Register all custom commands
import * as commands from './commands';
import * as childCommands from './commands/child-commands';

type OmitSubjectArg<T> = T extends (subject: JQuery, ...args: infer P) => infer R ? (...args: P) => R : never;
type ChildCommandsType = { [K in keyof typeof childCommands]: OmitSubjectArg<(typeof childCommands)[K]> };
type CommandsType = typeof commands & ChildCommandsType;

declare global {
    // eslint-disable-next-line @typescript-eslint/no-namespace
    namespace Cypress {
        // eslint-disable-next-line @typescript-eslint/no-empty-interface
        interface Chainable extends CommandsType {}
    }
}

Object.entries(commands).forEach(([name, func]) => {
    Cypress.Commands.add(name as any, func);
});
Object.entries(childCommands).forEach(([name, func]) => {
    Cypress.Commands.add(name as any, { prevSubject: true }, func);
});

// https://docs.cypress.io/api/events/catalog-of-events#Uncaught-Exceptions
Cypress.on('uncaught:exception', (err, runnable, promise) => {
    // when the exception originated from an unhandled promise
    // rejection, the promise is provided as a third argument
    // you can turn off failing the test in this case
    if (promise) {
        return false;
    }
    // we still want to ensure there are no other unexpected
    // errors, so we let them fail the test
});
