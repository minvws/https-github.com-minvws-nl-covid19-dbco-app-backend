import type { JsonSchema } from '@dbco/ui-library';

/**
 * Schema will be wrapped in a `data` property by BE
 */
export const schema: JsonSchema = {
    type: 'object',
    properties: {
        foo: {
            type: 'object',
            properties: {
                string: {
                    type: 'string',
                },
                stringEmail: {
                    type: 'string',
                    format: 'email',
                },
                stringDate: {
                    type: 'string',
                    format: 'date',
                },
                stringDateTime: {
                    type: 'string',
                    format: 'date-time',
                },
                stringOneOf: {
                    type: 'string',
                    oneOf: [
                        {
                            const: 'tempora',
                            title: 'Tempora voluptate temporibus',
                        },
                        {
                            const: 'ipsa',
                            title: 'Ipsa aut illum similique nihil tempore consectetur',
                        },
                        {
                            const: 'doloribus',
                            title: 'Doloribus quo vitae voluptatem',
                        },
                    ],
                },
                stringEnum: {
                    type: 'string',
                    enum: ['DE', 'IT', 'JP', 'US', 'RU', 'Other'],
                },
                arrayString: {
                    type: 'array',
                    items: {
                        type: 'string',
                    },
                },
                // number: {
                //     type: 'number',
                // },
                integer: {
                    type: 'integer',
                },
                boolean: {
                    type: 'boolean',
                },
            },
        },
    },
};
