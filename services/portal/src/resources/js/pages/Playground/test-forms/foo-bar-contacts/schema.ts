import type { JsonSchema } from '@dbco/ui-library';

export const schema: JsonSchema = {
    type: 'object',
    properties: {
        foo: {
            type: 'object',
            properties: {
                bar: {
                    type: 'string',
                },
            },
        },
        contacts: {
            type: 'hasMany',
            listProperties: ['person.fullName'],
        } as any,
    },
};

export const contactSchema = {
    type: 'object',
    properties: {
        person: {
            type: 'object',
            properties: {
                fullName: { type: 'string' },
                dob: { type: 'string', format: 'date' },
            },
        },
    },
} as const;

export const schemaFE: JsonSchema = {
    type: 'object',
    properties: {
        ...schema.properties,
        contacts: {
            type: 'object',
            properties: {
                data: {
                    type: 'array',
                    items: {
                        type: 'object',
                        properties: {
                            data: {
                                type: 'object',
                                properties: {
                                    person: {
                                        type: 'object',
                                        properties: {
                                            name: contactSchema.properties.person.properties.fullName,
                                        },
                                    },
                                },
                            },
                        },
                    },
                },
            },
        },
    },
};
