import { faker } from '@faker-js/faker';
import type { FormRootData } from '../../types';
import { removeChildFormData } from './remove-child-form-data';

describe('remove-form-meta-data', () => {
    const randomArray = faker.lorem.words(50).split(' ');

    it('should return remove meta data from form data, but not mutate the source', () => {
        const $config = faker.lorem.words();
        const formData: FormRootData = {
            $config,
            $links: {},
            $validationErrors: [],
            $forms: {},
            foo: {
                value: randomArray[0],
                array: randomArray,
                childForm: {
                    value: randomArray[1],
                    $links: {},
                },
                secondChildForm: {
                    value: randomArray[2],
                    $links: {},
                },
                deep: {
                    deepValue: randomArray[3],
                    deepChildForm: {
                        value: randomArray[4],
                        $links: {},
                    },
                },
            },
        };

        expect(removeChildFormData(formData)).toEqual({
            $config,
            $links: {},
            $validationErrors: [],
            $forms: {},
            foo: {
                value: randomArray[0],
                array: randomArray,
                deep: {
                    deepValue: randomArray[3],
                },
            },
        });
    });
});
