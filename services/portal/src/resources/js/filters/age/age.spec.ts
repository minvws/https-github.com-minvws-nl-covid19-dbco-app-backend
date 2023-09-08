import { fakerjs } from '@/utils/test';
import { subYears } from 'date-fns';
import { age } from './age';

describe('index_age', () => {
    it('should return the age', () => {
        const years = fakerjs.number.int({ max: 100 }) + 1;
        expect(age(subYears(Date.now(), years).toISOString())).toEqual(`${years}`);
    });

    it('should return an empty string for an invalid age', () => {
        expect(age(fakerjs.lorem.word())).toEqual('');
    });

    it.each([[''], [null], [undefined]])('should return an empty string for %s', (value) => {
        expect(age(value)).toEqual('');
    });
});
