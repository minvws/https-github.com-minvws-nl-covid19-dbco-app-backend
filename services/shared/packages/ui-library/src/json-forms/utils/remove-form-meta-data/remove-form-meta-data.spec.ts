import { faker } from '@faker-js/faker';
import type { FormRootData } from '../../types';
import { removeFormMetaData } from './remove-form-meta-data';

describe('remove-form-meta-data', () => {
    it('should return remove meta data from form data, but not mutate the source', () => {
        const $config = faker.lorem.word();
        const formData: FormRootData = {
            $config,
            $links: {},
            $validationErrors: [],
            $forms: {},
        };
        expect(removeFormMetaData(formData)).toEqual({});
        expect(formData.$config).toBe($config);
    });
});
