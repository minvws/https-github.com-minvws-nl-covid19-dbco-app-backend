import { fakerjs } from '../test';
import { getAssignmentToken } from '@dbco/portal-api/token';

describe('getAssignmentToken', () => {
    it('should return {} when token is NOT provided', () => {
        expect(getAssignmentToken()).toEqual({});
    });

    it('should return assignment token header when token is provided', () => {
        const token = fakerjs.string.sample();
        expect(getAssignmentToken(token)).toEqual({ 'Assignment-Token': token });
    });
});
