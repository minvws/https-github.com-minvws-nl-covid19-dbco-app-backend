import { getCaseApiHandlers } from '@/__mocks__/case.msw';
import { getCaseListApiHandlers } from '@/__mocks__/caseList.msw';
import { getUserApiHandlers } from '@/__mocks__/user.msw';
import { rest } from 'msw';

export const handlers = [
    rest.get('/ping', (_req, res, ctx) => {
        return res(ctx.status(200));
    }),
    ...getCaseApiHandlers(),
    ...getCaseListApiHandlers(),
    ...getUserApiHandlers(),
];
