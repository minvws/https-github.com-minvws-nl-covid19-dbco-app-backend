import { rest } from 'msw';

export const getCaseListApiHandlers = () => [
    rest.get(`/api/caselists`, (req, res, ctx) => {
        return res(
            ctx.json({
                from: null,
                to: null,
                total: 0,
                currentPage: 1,
                lastPage: 1,
                data: [
                    {
                        uuid: 'eb9b9606-a589-47bb-8377-3338b43adffc',
                        name: 'Test lijst',
                        isDefault: false,
                        isQueue: false,
                        assignedCasesCount: 0,
                        unassignedCasesCount: 0,
                        completedCasesCount: 0,
                        archivedCasesCount: 0,
                    },
                ],
            })
        );
    }),
    rest.get(`/api/cases/counts`, (req, res, ctx) => {
        return res(
            ctx.json({
                unassigned: 1,
                assigned: 0,
                outsourced: 0,
                queued: 0,
                completed: 0,
            })
        );
    }),
];
