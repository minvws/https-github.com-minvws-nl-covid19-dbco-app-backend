import { fakerjs } from '@/utils/test';
import { categoryFormat, placeCategoryImageClass } from './category';

describe('categoryFormat', () => {
    it('should return a category string representation', () => {
        expect(categoryFormat('2b')).toEqual('2B - Nauw contact');
    });

    it('is not case sensitive', () => {
        expect(categoryFormat('2b')).toEqual('2B - Nauw contact');
        expect(categoryFormat('2B')).toEqual('2B - Nauw contact');
    });

    it('should return the input if the category does not match', () => {
        const value = fakerjs.lorem.word();
        expect(categoryFormat(value)).toEqual(value);
    });

    it.each([[''], [null], [undefined]])('should return an empty string if the input is %s', (value) => {
        expect(categoryFormat(value)).toEqual('');
    });
});

describe('placeCategoryImageClass', () => {
    it.each([
        ['horeca', 'icon--category-horeca'],
        ['restaurant', 'icon--category-horeca'],
        ['asielzoekerscentrum', 'icon--category-opvang'],
    ])('should return the correct image class for %s', (value, expectedClass) => {
        expect(placeCategoryImageClass(value)).toEqual(expectedClass);
    });

    it.each([[''], [null], [undefined], ['__UNKOWN__']])('should return a fallback class  is %s', (value) => {
        expect(placeCategoryImageClass(value)).toEqual('icon--category-onbekend');
    });
});
