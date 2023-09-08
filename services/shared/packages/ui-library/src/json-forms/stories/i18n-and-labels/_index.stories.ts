import type { Meta } from '@storybook/vue';
import { setupJsonFormsStory } from '../setup-json-forms-story';
import { props as i18n } from './i18n';
import { props as i18nErrors } from './i18n-errors';
import { props as i18nErrorsMultiple } from './i18n-errors-multiple';
import { onBeforeMount, onUnmounted } from 'vue';

export default {
    title: 'JsonForms/Labels and i18n',
    parameters: {
        docs: {
            description: {
                component: `<br/>## Labels
                
Labels are derived in different ways. Either via:

- the \`title\` property on the schema or uiSchema
- the \`i18n\` property on the schema or uiSchema (which mapped to the \`i18n\` resource)
- the path of the field (e.g. \`example.three\` which is also mapped to the \`i18n\` resource)
- of no title or i18n is set, the label will be derived from the property name

[Also see the official documentation.](https://jsonforms.io/docs/labels/)

## i18n

A default \`i18n\` configuration is used for setting the correct error messages and some default labels. This can be overridden by passing a custom 
\`i18nResource\` configuration to the \`JsonForms\` component. This configuration will be merged with the default configuration.

For fields JsonForms uses a \`label\` and \`description\` property of the \`i18n\` key that is set. So for example: 
to set the label for a \`firstName\` field you would define a \`firstName.label\` property in the \`i18nResource\` configuration.

Errors will be mapped to the \`error.{keyword}\` value. If no message is found for the keyword, the default message will be used.

[Also see the official documentation.](https://jsonforms.io/docs/i18n/)
                `,
            },
        },
    },
} as Meta;

export const BasicLabels = setupJsonFormsStory({ props: i18n });

export const Errors = setupJsonFormsStory({
    props: i18nErrors,
    setup() {
        const originalErrorLog = console.error;
        onBeforeMount(() => {
            // Supresses the Vue warning about invalid props
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            function filterVueWarnLog(...args: any[]) {
                const [message] = args;
                if (message && /\[Vue warn\]: Invalid prop: type check failed for prop/.test(message)) return;
                originalErrorLog.apply(console, args);
            }

            console.error = filterVueWarnLog;
        });

        onUnmounted(() => {
            console.error = originalErrorLog;
        });
    },
});

export const ErrorsMultiple = setupJsonFormsStory({ props: i18nErrorsMultiple });
