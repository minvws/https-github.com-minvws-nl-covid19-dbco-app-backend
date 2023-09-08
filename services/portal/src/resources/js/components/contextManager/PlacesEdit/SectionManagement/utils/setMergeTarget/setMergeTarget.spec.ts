import type { CurrentSection } from '../../sectionManagementTypes';

import setMergeTarget from './setMergeTarget';

const mainSection: CurrentSection = {
    label: 'Test1',
    indexCount: 0,
    uuid: '456',
};

describe('setMergeTarget', () => {
    it('should return target uuid if section to be merged into already exists.', () => {
        expect(setMergeTarget(mainSection, false)).toStrictEqual('456');
    });
    it('should return target as Section, including label for creation, if section to be merged into only exists in local state.', () => {
        expect(setMergeTarget(mainSection, true)).toStrictEqual({
            label: 'Test1',
            indexCount: 0,
            uuid: '456',
        });
    });
});
