import type { CallQueue } from '../../sectionManagementTypes';
import checkIfTargetNotYetCreated from './checkIfTargetNotYetCreated';

const createQueue: CallQueue['createQueue'] = [
    {
        label: 'Test1',
        indexCount: 0,
        uuid: '1',
    },
    {
        label: 'Test2',
        indexCount: 0,
        uuid: '2',
    },
    {
        label: 'Test3',
        indexCount: 0,
        uuid: '3',
    },
];

describe('checkIfTargetNotYetCreated', () => {
    it('should return true if uuid found in createQueue', () => {
        expect(checkIfTargetNotYetCreated('1', createQueue)).toBe(true);
    });
    it('should return false if uuid NOT found in createQueue', () => {
        expect(checkIfTargetNotYetCreated('4', createQueue)).toBe(false);
    });
});
