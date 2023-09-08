import { rest } from 'msw';

export const getCaseApiHandlers = () => [
    rest.get(`/api/cases/unassigned`, (req, res, ctx) => {
        return res(
            ctx.json({
                from: 1,
                to: 4,
                currentPage: 1,
                data: [
                    {
                        uuid: 'e17bfed0-37a8-40fc-b0d6-6c180254c6c9',
                        caseId: 'AB1-555-123',
                        hpzoneNumber: '22345678',
                        contactsCount: 0,
                        dateOfBirth: '2019-07-21',
                        dateOfTest: null,
                        statusIndexContactTracing: null,
                        statusExplanation: '',
                        createdAt: '2022-01-28T16:48:43Z',
                        updatedAt: '2022-01-28T16:48:43Z',
                        organisation: {
                            uuid: '00000000-0000-0000-0000-000000000000',
                            abbreviation: 'GGD1',
                            name: 'Demo GGD1',
                            isCurrent: true,
                        },
                        assignedOrganisation: null,
                        assignedCaseList: null,
                        assignedUser: null,
                        isEditable: true,
                        isDeletable: true,
                        isClosable: false,
                        isAssignable: true,
                        isApproved: null,
                        label: null,
                        plannerView: 'unassigned',
                        bcoStatus: 'draft',
                        wasOutsourced: false,
                        wasOutsourcedToOrganisation: null,
                        priority: 0,
                        caseLabels: [],
                        hasNotes: false,
                    },
                ],
            })
        );
    }),

    rest.get(`/api/cases/assigned`, (req, res, ctx) => {
        return res(
            ctx.json({
                from: null,
                to: null,
                currentPage: 1,
                data: [],
            })
        );
    }),

    rest.get('/api/cases/assignment/all-user-options', (req, res, ctx) => {
        return res(
            ctx.json({
                options: [
                    {
                        type: 'option',
                        label: 'Demo GGD1 Dossierkwaliteit',
                        isSelected: false,
                        isEnabled: true,
                        assignmentType: 'user',
                        assignment: {
                            assignedUserUuid: '00000000-0000-0000-0000-000000000007',
                        },
                    },
                ],
            })
        );
    }),
    rest.get('/api/cases/:uuid/assignment/options', (req, res, ctx) =>
        res(
            ctx.json({
                options: [
                    {
                        type: 'option',
                        label: 'Niet toegewezen',
                        isSelected: false,
                        isEnabled: true,
                        assignment: {
                            assignedUserUuid: null,
                        },
                    },
                    {
                        type: 'option',
                        label: 'Wachtrij',
                        isSelected: false,
                        isEnabled: true,
                        isQueue: true,
                        assignmentType: 'caseList',
                        assignment: {
                            assignedCaseListUuid: '96111545-465c-4c8d-9cc7-c1d614c56d63',
                        },
                    },
                    {
                        type: 'menu',
                        label: 'Lijsten',
                        options: [
                            {
                                type: 'option',
                                label: 'Geen lijst',
                                isSelected: true,
                                isEnabled: false,
                                isQueue: null,
                                assignmentType: 'caseList',
                                assignment: {
                                    assignedCaseListUuid: null,
                                },
                            },
                        ],
                        isEnabled: true,
                    },
                    {
                        type: 'menu',
                        label: 'Uitbesteden',
                        options: [
                            {
                                type: 'option',
                                label: 'Demo LS1',
                                isSelected: false,
                                isEnabled: true,
                                assignmentType: 'organisation',
                                assignment: {
                                    assignedOrganisationUuid: '10000000-0000-0000-0000-000000000000',
                                },
                            },
                        ],
                    },
                    {
                        type: 'separator',
                    },
                    {
                        type: 'option',
                        label: 'Demo GGD1 Gebruiker',
                        isSelected: true,
                        isEnabled: true,
                        assignmentType: 'user',
                        assignment: {
                            assignedUserUuid: null,
                        },
                    },
                    {
                        type: 'option',
                        label: 'Demo GGD1 Gebruiker & Contextbeheerder',
                        isSelected: false,
                        isEnabled: true,
                        assignmentType: 'user',
                        assignment: {
                            assignedUserUuid: '00000000-0000-0000-0000-100000000002',
                        },
                    },
                    {
                        type: 'option',
                        label: 'Demo GGD1 Gebruiker & Werkverdeler',
                        isSelected: false,
                        isEnabled: true,
                        assignmentType: 'user',
                        assignment: {
                            assignedUserUuid: '00000000-0000-0000-0000-000000000002',
                        },
                    },
                ],
            })
        )
    ),
    rest.put(
        '/api/cases/:uuid/assignment',
        (req, res, ctx) => res(ctx.status(204)) // no-content response
    ),
    rest.get('/api/caselabels', (req, res, ctx) => {
        return res(
            ctx.json([
                {
                    uuid: '9b816cce-0e99-4509-af2b-0db377e59c74',
                    label: 'Zorg',
                    is_selectable: true,
                },
                {
                    uuid: 'b9f32bff-5711-4f9f-b474-6abbb3547215',
                    label: 'Bewoner zorg',
                    is_selectable: true,
                },
                {
                    uuid: 'fb5631b3-b3ee-4c00-af87-0f95f45e038e',
                    label: 'Medewerker zorg',
                    is_selectable: true,
                },
                {
                    uuid: 'bfea8c2f-3b62-45cb-a892-f28ff7877c19',
                    label: 'School',
                    is_selectable: true,
                },
                {
                    uuid: '01b40157-b157-4243-8aff-f4936541203c',
                    label: 'Contactberoep',
                    is_selectable: true,
                },
                {
                    uuid: '12b00e2b-b198-47a3-9755-058220b735f5',
                    label: 'Maatschappelijke instelling',
                    is_selectable: true,
                },
                {
                    uuid: 'c229717a-cee2-4f3f-93d4-4924daf28d67',
                    label: 'Scheepvaart opvarende',
                    is_selectable: true,
                },
                {
                    uuid: 'd893ccde-a3ee-4daf-85c9-682540291f43',
                    label: 'Vluchten',
                    is_selectable: true,
                },
                {
                    uuid: '969ba414-2273-49d2-b0c7-b5a37cb70fa9',
                    label: 'Buitenland',
                    is_selectable: true,
                },
                {
                    uuid: '45448be0-9ffc-4970-ad68-73447efdc4b5',
                    label: 'VOI/VOC',
                    is_selectable: true,
                },
                {
                    uuid: '8cce8344-b2d2-421a-ae74-659415421e81',
                    label: 'Herhaaluitslag',
                    is_selectable: true,
                },
                {
                    uuid: 'd70141ef-8b78-452b-a4cf-c3cf9eac3933',
                    label: 'Buiten meldportaal/CoronIT',
                    is_selectable: true,
                },
                {
                    uuid: 'afb8a52a-6cc8-4ed7-8b55-04c2dd026880',
                    label: 'Steekproef',
                    is_selectable: true,
                },
                {
                    uuid: '073b3972-233e-41ac-8db4-9d9704f791bc',
                    label: 'Onvolledige gegevens',
                    is_selectable: true,
                },
                {
                    uuid: '2b023cc6-09da-4054-b89b-c33a062bb7c1',
                    label: 'Index weet uitslag niet',
                    is_selectable: true,
                },
                {
                    uuid: 'd05cb743-007e-4a4c-9511-c52a0ea9c2b7',
                    label: 'Uitbraak',
                    is_selectable: true,
                },
                {
                    uuid: '17137860-6abf-43e7-a5da-1ef447b74149',
                    label: 'Gezondheidsindicatie',
                    is_selectable: true,
                },
                {
                    uuid: 'c1093ec3-9f2e-4ea5-aa95-b10e398264b5',
                    label: 'Intake ingevuld',
                    is_selectable: false,
                },
                {
                    uuid: '31a5f595-234b-444c-a911-2426adde8c9a',
                    label: 'Uitgenodigd voor intake',
                    is_selectable: false,
                },
                {
                    uuid: '599660f0-1705-4759-b5a7-0d1cacde2d8a',
                    label: 'Osiris melding mislukt',
                    is_selectable: true,
                },
            ])
        );
    }),
];
