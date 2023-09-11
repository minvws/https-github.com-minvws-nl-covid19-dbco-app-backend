import { faker } from '@faker-js/faker';
import { unrefAll } from './unref-all';
import { isRef, ref } from 'vue';

describe('unref-all', () => {
    it('should be able unref all properties of an object', () => {
        const randomString = faker.lorem.word();
        const randomNumber = faker.number.int();

        const refCollection = {
            foo: ref(randomString),
            bar: ref(randomNumber),
        };

        expect(isRef(refCollection.foo)).toBe(true);
        expect(isRef(refCollection.bar)).toBe(true);

        const plainObject = unrefAll(refCollection);

        expect(isRef(plainObject.foo)).toBe(false);
        expect(isRef(plainObject.bar)).toBe(false);

        expect(plainObject.foo).toBe(randomString);
        expect(plainObject.bar).toBe(randomNumber);
    });
});
