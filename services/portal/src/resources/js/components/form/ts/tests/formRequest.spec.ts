import { getAllErrors, transformFragmentsValidationResult } from '../formRequest';

describe('getAllErrors', () => {
    it('should get notice errors from validationResult', () => {
        const validationResult = {
            notice: {
                failed: {
                    someField: {
                        Prohibited: [],
                    },
                },
                errors: {
                    someField: ['Let op: niet ok'],
                },
            },
        };

        const errors = getAllErrors(validationResult);

        expect(errors).toEqual({
            errors: {
                someField: '{"notice":["Let op: niet ok"]}',
            },
        });
    });

    it('should get warning errors from validationResult', () => {
        const validationResult = {
            warning: {
                failed: {
                    dateOfLastExposure: {
                        Prohibited: [],
                    },
                },
                errors: {
                    dateOfLastExposure: [
                        'Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case',
                    ],
                },
            },
        };

        const errors = getAllErrors(validationResult);

        expect(errors).toEqual({
            errors: {
                dateOfLastExposure:
                    '{"warning":["Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case"]}',
            },
        });
    });

    it('should get fatal errors from validationResult', () => {
        const validationResult = {
            fatal: {
                failed: {
                    'general.reference': {
                        Unique: ['covidcase', 'case_id', 'NULL', 'id', 'deleted_at', 'NULL'],
                    },
                    'contact.phone': { Phone: ['AUTO', 'NL'] },
                },
                errors: {
                    'general.reference': ['Referentie is al in gebruik.'],
                    'contact.phone': ['Telefoon moet een geldig telefoonnummer zijn.'],
                },
            },
        };

        const errors = getAllErrors(validationResult);

        expect(errors).toEqual({
            errors: {
                'contact.phone': '{"fatal":["Telefoon moet een geldig telefoonnummer zijn."]}',
                'general.reference': '{"fatal":["Referentie is al in gebruik."]}',
            },
        });
    });
});

describe('transformFragmentsValidationResult', () => {
    it('should get all errors by fragment from validationResult', () => {
        const validationResult = {
            someFragment: {
                notice: {
                    failed: {
                        someField: {
                            Prohibited: [],
                        },
                    },
                    errors: {
                        someField: ['Let op: niet ok'],
                    },
                },
                warning: {
                    failed: {
                        dateOfLastExposure: {
                            Prohibited: [],
                        },
                    },
                    errors: {
                        dateOfLastExposure: [
                            'Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case',
                        ],
                    },
                },
                fatal: {
                    failed: {
                        'general.reference': {
                            Unique: ['covidcase', 'case_id', 'NULL', 'id', 'deleted_at', 'NULL'],
                        },
                        'contact.phone': { Phone: ['AUTO', 'NL'] },
                    },
                    errors: {
                        'general.reference': ['Referentie is al in gebruik.'],
                        'contact.phone': ['Telefoon moet een geldig telefoonnummer zijn.'],
                    },
                },
            },
        };

        const errors = transformFragmentsValidationResult(validationResult);

        expect(errors).toEqual({
            someFragment: {
                errors: {
                    'contact.phone': '{"fatal":["Telefoon moet een geldig telefoonnummer zijn."]}',
                    dateOfLastExposure:
                        '{"warning":["Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case"]}',
                    'general.reference': '{"fatal":["Referentie is al in gebruik."]}',
                    someField: '{"notice":["Let op: niet ok"]}',
                },
            },
        });
    });

    it('should return errors with array keys', () => {
        const validationResult = {
            someFragment: {
                fatal: {
                    failed: {
                        'moments.0.day': {
                            Prohibited: [],
                        },
                    },
                    errors: {
                        'moments.0.day': [
                            'Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case',
                        ],
                    },
                },
            },
        };

        const errors = transformFragmentsValidationResult(validationResult);

        expect(errors).toEqual({
            someFragment: {
                errors: {
                    'moments.0.day':
                        '{"fatal":["Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case"]}',
                },
            },
        });
    });
});
