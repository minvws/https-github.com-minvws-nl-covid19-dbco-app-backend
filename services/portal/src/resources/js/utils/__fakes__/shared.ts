import { fakerjs } from '@/utils/test';

export const fakeError = {
    response: {
        data: {
            message: fakerjs.lorem.sentence(),
        },
        status: 404,
    },
};
