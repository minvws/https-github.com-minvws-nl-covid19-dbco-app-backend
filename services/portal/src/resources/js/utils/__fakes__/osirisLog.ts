import type { OsirisLogItem } from '@dbco/portal-api/osiris.dto';
import { fakerjs } from '@/utils/test';
import { createFakeDataGenerator } from './createFakeDataGenerator';
import { OsirisHistoryStatusV1 } from '@dbco/enum';

export const fakeLogItem = createFakeDataGenerator<OsirisLogItem>(() => ({
    caseIsReopened: false,
    osirisValidationResponse: {
        errors: fakerjs.custom.typedArray<string>(fakerjs.lorem.sentence(), 1, 2),
        messages: fakerjs.custom.typedArray<string>(fakerjs.lorem.sentence(), 1, 2),
        warnings: fakerjs.custom.typedArray<string>(fakerjs.lorem.sentence(), 1, 2),
    },
    time: fakerjs.date.recent().toISOString(),
    status: OsirisHistoryStatusV1.VALUE_success,
    uuid: fakerjs.string.uuid(),
}));
