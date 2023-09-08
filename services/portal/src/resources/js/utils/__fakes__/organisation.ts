import type { Organisation } from '@/store/userInfo/userInfoStore';
import { fakerjs } from '@/utils/test';
import { BcoPhaseV1 } from '@dbco/enum';

export const generateFakeOrganisation = (): Organisation => ({
    abbreviation: fakerjs.company.name(),
    bcoPhase: BcoPhaseV1.VALUE_1,
    hasOutsourceToggle: fakerjs.datatype.boolean(),
    isAvailableForOutsourcing: fakerjs.datatype.boolean(),
    type: fakerjs.lorem.word(),
    name: fakerjs.company.name(),
    uuid: fakerjs.string.uuid(),
});

export const fakeOrganisation = generateFakeOrganisation();
