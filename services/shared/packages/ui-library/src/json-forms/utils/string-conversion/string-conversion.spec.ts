import type { StringConversionType, StringConversionValue } from './string-conversion';
import { inputToInteger, inputToNumber, stringToValue, valueToString } from './string-conversion';

describe('string-conversion - input', () => {
    it.each<[string, number | undefined]>([
        ['', undefined],
        ['1', 1],
        ['1.1', 1.1],
        ['1.0009', 1.0009],
        ['-1.0009', -1.0009],
        ['-999.81', -999.81],
    ])('should convert input string %j to the number %j', (string, expectedNumber) => {
        expect(inputToNumber(string)).toEqual(expectedNumber);
    });

    it.each<[string, number | undefined]>([
        ['', undefined],
        ['1', 1],
        ['1.1', 1],
        ['1.0009', 1],
        ['-1.0009', -1],
        ['-999.81', -999],
    ])('should convert input string %j to the integer %j', (string, expectedNumber) => {
        expect(inputToInteger(string)).toEqual(expectedNumber);
    });
});

describe('string-conversion - value', () => {
    it.each<[StringConversionValue, string]>([
        [undefined, ''],
        [4, '4'],
        [1, '1'],
        [1.1, '1.1'],
        [-1.0092, '-1.0092'],
        ['-1.0092', '-1.0092'],
        ['-0.56', '-0.56'],
        ['foobar', 'foobar'],
        [true, 'true'],
        [false, 'false'],
    ])('should convert value %j to string %j ', (value, expectedString) => {
        expect(valueToString(value)).toEqual(expectedString);
    });

    it.each<[StringConversionType, string, StringConversionValue]>([
        // integer
        ['integer', '', undefined],
        ['integer', '4', 4],
        ['integer', '1', 1],
        ['integer', '1.1', 1.1],
        ['integer', '-1.0092', -1.0092],
        ['integer', 'NaN', NaN],
        ['integer', 'Infinity', Infinity],
        // number
        ['number', '', undefined],
        ['number', '4', 4],
        ['number', '1', 1],
        ['number', '1.1', 1.1],
        ['number', '-1.0092', -1.0092],
        ['number', 'NaN', NaN],
        ['number', 'Infinity', Infinity],
        // string
        ['string', '', undefined],
        ['string', '-1.0092', '-1.0092'],
        ['string', '-0.56', '-0.56'],
        ['string', 'foobar', 'foobar'],
        ['string', 'NaN', 'NaN'],
        ['string', 'Infinity', 'Infinity'],
        // boolean
        ['boolean', '', undefined],
        ['boolean', 'true', true],
        ['boolean', 'false', false],
    ])('should convert value %j with the type %j to string %j and back again', (type, string, expectedValue) => {
        const value = stringToValue(string, type);
        const valueString = valueToString(value);

        expect(value).toEqual(expectedValue);
        expect(valueString).toEqual(string);
    });

    it('should throw an error if the type is not supported', () => {
        expect(() => stringToValue('value', 'unknown type' as 'string')).toThrowError(
            'Unknown JSON schema type unknown type'
        );
    });
});
