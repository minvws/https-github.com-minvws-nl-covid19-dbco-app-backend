export const nl = {
    translation: {
        label: {
            required: '(Verplicht)',
        },
        error: {
            minLength: 'Deze waarde moet minimaal {{limit}} tekens bevatten',
            maxLength: 'Deze waarde mag maximaal {{limit}} tekens bevatten',
            required: 'Dit veld is verplicht',
            enum: 'Deze waarde moet een van de volgende waarden bevatten: {{allowedValues}}',
            type: 'Deze waarde moet van het type {{type}} zijn',
            const: 'Deze waarde moet {{allowedValue}} zijn',
            pattern: 'Deze waarde moet overeenkomen met het volgende patroon: {{pattern}}',
            minimum: 'Deze waarde moet groter zijn dan of gelijk zijn aan {{limit}}',
            maximum: 'Deze waarde moet kleiner zijn dan of gelijk zijn aan {{limit}}',
            exclusiveMinimum: 'Deze waarde moet groter zijn dan {{limit}}',
            exclusiveMaximum: 'Deze waarde moet kleiner zijn dan {{limit}}',
            multipleOf: 'Deze waarde moet een veelvoud zijn van {{multipleOf}}',
            minItems: 'Deze waarde moet minimaal {{limit}} items bevatten',
            maxItems: 'Deze waarde mag maximaal {{limit}} items bevatten',
            uniqueItems: 'Deze waarde moet unieke items bevatten',

            // The following situations are not (yet) supported by the JSON Forms library:
            minContains: 'Deze waarde moet minimaal {{limit}} items bevatten',
            maxContains: 'Deze waarde mag maximaal {{limit}} items bevatten',
        },
    },
} as const;
