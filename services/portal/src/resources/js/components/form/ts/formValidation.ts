const getValidation = (rule: string, label: string) => {
    switch (rule) {
        case 'email':
            return {
                validation: [['optional'], ['email']],
                messages: {
                    email: `${label} is geen geldig e-mailadres.`,
                },
            };
        case 'number':
            return {
                validation: [['number']],
                messages: {
                    number: `${label} moet een nummer zijn.`,
                },
            };
        case 'numeric':
            return {
                validation: [['optional'], ['matches', /^[0-9-]+$/]],
                messages: {
                    matches: `${label} mag enkel nummers bevatten.`,
                },
            };
        case 'houseNumber':
            return {
                validation: [['min', '1']],
                messages: {
                    min: `${label} ongeldig.`,
                },
            };
        case 'positive':
            return {
                validation: [['min', '1']],
                messages: {
                    min: `${label} moet groter of gelijk zijn aan 1.`,
                },
            };
        case 'zeroOrGreater':
            return {
                validation: [['min', '0']],
                messages: {
                    min: `${label} moet groter of gelijk zijn aan 0.`,
                },
            };
        case 'monsterNumber':
            return {
                validation: [['matches', /^\d{3}[a-zA-Z]\d{1,12}$/]],
                messages: {
                    matches: `Het ${label} is niet geldig.`,
                },
            };
        case 'caseNumber':
            return {
                validation: [['matches', /^([0-9]{6,8}|[a-zA-Z]{2}\d[-\s]?\d{3}[-\s]?\d{3})$/]],
                messages: {
                    matches: `Dit is geen geldig dossiernummer (AB1-234-567) of geldig HPZone-nummer (6 tot 8 cijfers)`,
                },
            };
        case 'hpZone':
            return {
                validation: [['matches', /^(\d{7,8})$/]],
                messages: {
                    matches: `Het ${label} moet uit 7 of 8 cijfers bestaan.`,
                },
            };
        case 'hpZoneRetro':
            return {
                validation: [['optional'], ['matches', /^(\d{6,8})$/]],
                messages: {
                    matches: `Let op: dit is geen geldig ${label}. Een ${label} bestaat uit 6 tot 8 cijfers.`,
                },
            };
        case 'ComplianceCaseNr':
            return {
                validation: ['optional'],
                messages: {
                    matches: `Er is geen dossier gevonden met dit nummer. Een HPZone nummer bestaat uit 7 tekens en een BCO Portaal- of monsternummer uit 9 tekens.`,
                },
            };
        case 'postalCode':
            return {
                // 1234AA
                validation: [['matches', /^[1-9][0-9]{3} ?(?!sa|sd|ss)[a-z]{2}$/i]],
                messages: {
                    matches: `${label} onjuist.`,
                },
            };
        case 'optional':
            return {
                validation: [['optional']],
                messages: {},
            };
        case 'required':
            return {
                validation: [['required']],
                messages: {
                    required: `${label} is verplicht.`,
                },
            };

        // Default VueFormulate validation or no validation necessary
        case 'date':
        case 'text':
            return {
                validation: [],
                messages: {},
            };
    }

    return null;
};

export const validation = (rules: string | string[], label: string) => {
    let validation: any[] = [];
    let messages = {};

    rules = Array.isArray(rules) ? rules : [rules];
    rules.forEach((item) => {
        const rule = getValidation(item, label);
        if (!rule) {
            console.warn(`Requested validation rule '${item}' does not exist, field: '${label}'`);
            return;
        }

        validation = [...validation, ...rule.validation];
        messages = { ...messages, ...rule.messages };
    });

    return {
        validation,
        'validation-messages': messages,
    };
};
