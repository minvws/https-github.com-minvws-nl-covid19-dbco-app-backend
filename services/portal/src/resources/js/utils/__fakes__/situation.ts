import type { PlaceSituation } from '@dbco/portal-api/place.dto';
import { fakerjs } from '../test';

export const fakeSituation = (): PlaceSituation => ({
    uuid: fakerjs.string.uuid(),
    name: fakerjs.lorem.word(),
    value: fakerjs.lorem.word(),
});
