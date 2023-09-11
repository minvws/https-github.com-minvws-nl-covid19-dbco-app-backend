import type { CallQueue, CurrentSection } from '../sectionManagementTypes';

import mergeSectionsInCallQueue from './mergeSectionsInCallQueue';

const mainSection1: CurrentSection = {
    label: 'Random',
    uuid: '4535346',
    indexCount: 0,
};

const mainSection2: CurrentSection = {
    label: 'Randomest',
    uuid: '675675',
    indexCount: 0,
};

const mergeSections1: CurrentSection[] = [
    {
        label: 'Test2',
        indexCount: 0,
        uuid: '2',
    },
    {
        label: 'Test4',
        indexCount: 0,
        uuid: '4',
    },
    {
        label: 'Test5',
        indexCount: 0,
        uuid: '5',
    },
];

const mergeSections2: CurrentSection[] = [
    {
        label: 'Test6',
        indexCount: 0,
        uuid: '6',
    },
    {
        label: 'Test7',
        indexCount: 0,
        uuid: '7',
    },
    {
        label: 'Random',
        uuid: '4535346',
        indexCount: 0,
    },
];

const callQueue1: CallQueue = {
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

const callQueue2: CallQueue = {
    changeLabelQueue: [],
    createQueue: [
        {
            label: 'Test',
            uuid: '123',
            indexCount: 0,
        },
    ],
    mergeQueue: [
        {
            target: mainSection1.uuid,
            payload: ['2', '4', '5'],
        },
    ],
};

describe('mergeSectionsInCallQueue', () => {
    it('should not update call queue if there is no payload', () => {
        expect(mergeSectionsInCallQueue(mainSection1, [], callQueue1)).toStrictEqual(callQueue1);
    });
    it('should update call queue with merge', () => {
        expect(mergeSectionsInCallQueue(mainSection1, mergeSections1, callQueue1)).toStrictEqual(callQueue2);
    });
    it('should update call queue with merge: previous target', () => {
        expect(mergeSectionsInCallQueue(mainSection2, mergeSections2, callQueue2)).toStrictEqual({
            changeLabelQueue: [],
            createQueue: [
                {
                    label: 'Test',
                    uuid: '123',
                    indexCount: 0,
                },
            ],
            mergeQueue: [
                {
                    target: mainSection2.uuid,
                    payload: ['2', '4', '5', '6', '7', '4535346'],
                },
            ],
        });
    });
});
