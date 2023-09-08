/* c8 ignore start */

import { flushCallStack } from './flush-callstack';

const logColorGray = '\x1b[2m\x1b[37m';

type ConsoleLogMethod = 'log' | 'debug' | 'info' | 'warn' | 'error';
type ConsoleLogMessage = {
    message: string;
    stack: string;
};
type ThrowLogConfig = {
    logMethods: ConsoleLogMethod[];
    ignoreMessages?: string[];
};

let isConfigured = false;
let ignoreLogMessages: string[];
let logMessages: ConsoleLogMessage[] = [];

beforeEach(() => {
    logMessages = [];
});

afterEach(async () => {
    await flushCallStack();

    if (logMessages.length) {
        const errorMessages = logMessages.map(({ message, stack }) => `${message}\n${logColorGray}${stack}`);
        throw new Error(errorMessages.join('\n\n'));
    }
});

function patchConsoleMethod(method: ConsoleLogMethod) {
    window.console[method] = (message?: unknown) => {
        const logMessage = typeof message === 'string' ? message : '';

        if (!!logMessage && ignoreLogMessages.includes(logMessage)) {
            return;
        }

        const { stack } = new Error();
        if (stack) {
            // Save the callstack and error message so we can throw this after the test.
            // Throwing the error right away doesn't always make the test fail
            // and can be accidentally caught and suppressed.
            const stackWithoutFirstLine = stack.substring(stack.indexOf('\n') + 1);
            logMessages.push({ message: logMessage, stack: stackWithoutFirstLine });
        }
    };
}

export function throwOnConsoleLog({ logMethods, ignoreMessages }: ThrowLogConfig) {
    if (isConfigured) {
        throw new Error('Can only configure "throw on log" once!');
    }

    isConfigured = true;
    ignoreLogMessages = ignoreMessages || [];
    logMethods.forEach(patchConsoleMethod);
}
