import { fakerjs } from '@/utils/test';
import { truncate } from './truncate';

describe('truncate', () => {
    it.each([
        [
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur atque.',
            'Aspernatur architecto praesentium et ...',
            40,
        ],
        [
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur atque.',
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur atque.',
            101,
        ],
        [
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur atque.',
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur at...',
            100,
        ],
        [
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur atque.',
            'Aspernatur architecto praesentium et dicta repudiandae dolorem eos dicta rerum ea consequuntur at...',
            undefined,
        ],
    ])('should truncate %s to %s', (input, expected, limit) => {
        expect(truncate(input, limit)).toStrictEqual(expected);
    });

    it.each([
        [null, fakerjs.number.int()],
        [undefined, fakerjs.number.int()],
    ])('should return an empty string if the input is %s', (input, limit) => {
        expect(truncate(input, limit)).toStrictEqual('');
    });
});
