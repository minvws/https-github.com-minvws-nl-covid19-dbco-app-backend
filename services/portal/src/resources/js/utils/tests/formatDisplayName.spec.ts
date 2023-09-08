import { formatDisplayName } from '../formatDisplayName';
import { fakerjs } from '../test';

const fakeFirstName = fakerjs.person.firstName();
const fakeInitials = `${fakerjs.string.alpha()}.${fakerjs.string.alpha()}.${fakerjs.string.alpha()}.`;
const fakeLastName = fakerjs.person.lastName();

describe('formatDisplayName', () => {
    it.each([
        [undefined, fakeInitials, fakeLastName, `${fakeInitials.toUpperCase()} ${fakeLastName}`],
        [null, fakeInitials, fakeLastName, `${fakeInitials.toUpperCase()} ${fakeLastName}`],
        ['', fakeInitials, fakeLastName, `${fakeInitials.toUpperCase()} ${fakeLastName}`],
        [null, null, fakeLastName, `${fakeLastName}`],
        [null, fakeInitials, null, `${fakeInitials.toUpperCase()}`],
        [fakeFirstName, fakeInitials, fakeLastName, `${fakeFirstName} (${fakeInitials.toUpperCase()}) ${fakeLastName}`],
        [fakeFirstName, undefined, fakeLastName, `${fakeFirstName} ${fakeLastName}`],
        [fakeFirstName, null, fakeLastName, `${fakeFirstName} ${fakeLastName}`],
        [fakeFirstName, '', fakeLastName, `${fakeFirstName} ${fakeLastName}`],
        [fakeFirstName, fakeInitials, undefined, `${fakeFirstName} (${fakeInitials.toUpperCase()})`],
        [fakeFirstName, fakeInitials, null, `${fakeFirstName} (${fakeInitials.toUpperCase()})`],
        [fakeFirstName, fakeInitials, '', `${fakeFirstName} (${fakeInitials.toUpperCase()})`],
    ])('should correctly format display name', (givenFirstName, givenInitials, givenLastName, expectedFormat) => {
        expect(formatDisplayName(givenFirstName, givenInitials, givenLastName)).toStrictEqual(expectedFormat);
    });
});
