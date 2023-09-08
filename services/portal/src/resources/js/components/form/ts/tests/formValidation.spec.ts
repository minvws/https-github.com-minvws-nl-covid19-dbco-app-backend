import { validation } from '../formValidation';

describe('formValidation', () => {
    it('should return a validation message for each rule in rules array', () => {
        expect(validation(['email', 'number'], 'TestLabel')).toMatchObject({
            validation: [['optional'], ['email'], ['number']],
            'validation-messages': {
                email: 'TestLabel is geen geldig e-mailadres.',
                number: 'TestLabel moet een nummer zijn.',
            },
        });
    });

    it('should return a validation message, even if rules is not an array', () => {
        expect(validation('email', 'TestLabel')).toMatchObject({
            validation: [['optional'], ['email']],
            'validation-messages': { email: 'TestLabel is geen geldig e-mailadres.' },
        });
    });

    it('should allow for any rule, but return an empty validation if the rule isnt in the list of rules.', () => {
        const warn = console.warn;
        console.warn = vi.fn();
        expect(validation(['doesnotexist'], 'TestLabel')).toMatchObject({ validation: [], 'validation-messages': {} });
        expect(console.warn).toBeCalledWith(
            `Requested validation rule 'doesnotexist' does not exist, field: 'TestLabel'`
        );
        console.warn = warn;
    });

    it('should allow a field to be optional', () => {
        expect(validation(['optional', 'number'], 'TestLabel')).toMatchObject({
            validation: [['optional'], ['number']],
            'validation-messages': {},
        });
    });

    it('should return the right validation if the rule is "email"', () => {
        expect(validation(['email'], 'TestLabel')).toMatchObject({
            validation: [['optional'], ['email']],
            'validation-messages': { email: 'TestLabel is geen geldig e-mailadres.' },
        });
    });

    it('should return the right validation if the rule is "number"', () => {
        expect(validation(['number'], 'TestLabel')).toMatchObject({
            validation: [['number']],
            'validation-messages': { number: 'TestLabel moet een nummer zijn.' },
        });
    });

    it('should return the right validation if the rule is "numeric"', () => {
        expect(validation(['numeric'], 'TestLabel')).toMatchObject({
            validation: [['optional'], ['matches', RegExp(/^[0-9-]+$/)]],
            'validation-messages': { matches: 'TestLabel mag enkel nummers bevatten.' },
        });
    });

    it('should return the right validation if the rule is "caseNumber"', () => {
        expect(validation(['caseNumber'], 'TestLabel')).toMatchObject({
            validation: [['matches', RegExp(/^([0-9]{6,8}|[a-zA-Z]{2}\d[-\s]?\d{3}[-\s]?\d{3})$/)]],
            'validation-messages': {
                matches: 'Dit is geen geldig dossiernummer (AB1-234-567) of geldig HPZone-nummer (6 tot 8 cijfers)',
            },
        });
    });

    it('should return the right validation if the rule is "hpZone"', () => {
        expect(validation(['hpZone'], 'TestLabel')).toMatchObject({
            validation: [['matches', RegExp(/^(\d{7}|\d{8})$/)]],
            'validation-messages': { matches: 'Het TestLabel moet uit 7 of 8 cijfers bestaan.' },
        });
    });

    it('should return the right validation if the rule is "hpZoneRetro"', () => {
        expect(validation(['hpZoneRetro'], 'TestLabel')).toMatchObject({
            validation: [['optional'], ['matches', RegExp(/^(\d{6}|\d{7}|\d{8})$/)]],
            'validation-messages': {
                matches: 'Let op: dit is geen geldig TestLabel. Een TestLabel bestaat uit 6 tot 8 cijfers.',
            },
        });
    });

    it('should return the right validation if the rule is "postalCode"', () => {
        expect(validation(['postalCode'], 'TestLabel')).toMatchObject({
            validation: [['matches', RegExp(/^[1-9][0-9]{3} ?(?!sa|sd|ss)[a-z]{2}$/i)]],
            'validation-messages': { matches: 'TestLabel onjuist.' },
        });
    });

    it('should return the right validation if the rule is "required"', () => {
        expect(validation(['required'], 'TestLabel')).toMatchObject({
            validation: [['required']],
            'validation-messages': { required: 'TestLabel is verplicht.' },
        });
    });

    it('should return the right validation if the rule is "houseNumber"', () => {
        expect(validation(['houseNumber'], 'TestLabel')).toMatchObject({
            validation: [['min', '1']],
            'validation-messages': { min: 'TestLabel ongeldig.' },
        });
    });

    it('should return the right validation if the rule is "positive"', () => {
        expect(validation(['positive'], 'TestLabel')).toMatchObject({
            validation: [['min', '1']],
            'validation-messages': { min: 'TestLabel moet groter of gelijk zijn aan 1.' },
        });
    });
    it('should return the right validation if the rule is "positive"', () => {
        expect(validation(['zeroOrGreater'], 'TestLabel')).toMatchObject({
            validation: [['min', '0']],
            'validation-messages': { min: 'TestLabel moet groter of gelijk zijn aan 0.' },
        });
    });
});
