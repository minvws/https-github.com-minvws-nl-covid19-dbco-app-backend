import type { CallQueue } from '../sectionManagementTypes';

import changeSectionLabelInQueue from './changeSectionLabelInQueue';

const callQueue: CallQueue = {
    changeLabelQueue: [],
    createQueue: [
        {
            label: 'Test',
            uuid: '123',
            indexCount: 0,
        },
    ],
    mergeQueue: [],
};

describe('changeSectionLabelInQueue', () => {
    it('should change label in create queue if section is there', () => {
        expect(changeSectionLabelInQueue('Testhal', '123', callQueue)).toStrictEqual({
            changeLabelQueue: [],
            createQueue: [
                {
                    label: 'Testhal',
                    uuid: '123',
                    indexCount: 0,
                },
            ],
            mergeQueue: [],
        });
    });
    it('should add name change to queue if section is not in crete queue', () => {
        expect(changeSectionLabelInQueue('Entreehal', '234', callQueue)).toStrictEqual({
            changeLabelQueue: [
                {
                    label: 'Entreehal',
                    uuid: '234',
                },
            ],
            createQueue: [
                {
                    label: 'Testhal',
                    uuid: '123',
                    indexCount: 0,
                },
            ],
            mergeQueue: [],
        });
    });
});
