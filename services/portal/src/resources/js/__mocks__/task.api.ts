export const getTasks = vi.fn(() =>
    Promise.resolve({
        tasks: [
            {
                uuid: '21683852-ef7b-4380-b363-92c3fb15eaf2',
                caseUuid: '8aa7348e-b7e0-4758-be90-3153ff75502a',
                internalReference: '',
                taskType: 'contact',
                source: 'portal',
                label: 'test ttttttty',
                derivedLabel: null,
                category: '2b',
                taskContext: null,
                nature: null,
                dateOfLastExposure: '2021-08-06',
                contactDates: null,
                communication: 'index',
                informedByIndexAt: null,
                informedByStaffAt: null,
                createdAt: '2021-08-06T08:56:25.000000Z',
                updatedAt: null,
                deletedAt: null,
                questionnaireUuid: null,
                progress: null,
                exportId: null,
                exportedAt: null,
                copiedAt: null,
                status: 'open',
                group: 'contact',
                isSource: false,
                dossierNumber: null,
                email: null,
                firstname: null,
                lastname: null,
                informStatus: 'unreachable',
                pseudoBsnGuid: null,
                accessible: true,
            },
            {
                uuid: '243628b6-f9e9-425c-a3ca-743a6266f6b3',
                caseUuid: '8aa7348e-b7e0-4758-be90-3153ff75502a',
                internalReference: '',
                taskType: 'contact',
                source: 'portal',
                label: 'fgfsdgdsgf tyjghjfghjdsfh',
                derivedLabel: null,
                category: '2b',
                taskContext: null,
                nature: null,
                dateOfLastExposure: '2021-08-05',
                contactDates: null,
                communication: 'index',
                informedByIndexAt: null,
                informedByStaffAt: null,
                createdAt: '2021-08-06T08:16:13.000000Z',
                updatedAt: null,
                deletedAt: null,
                questionnaireUuid: null,
                progress: null,
                exportId: null,
                exportedAt: null,
                copiedAt: null,
                status: 'open',
                group: 'contact',
                isSource: false,
                dossierNumber: null,
                email: null,
                firstname: null,
                lastname: null,
                informStatus: 'uninformed',
                pseudoBsnGuid: null,
                accessible: true,
            },
        ],
    })
);
