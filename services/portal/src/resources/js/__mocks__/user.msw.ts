import { rest } from 'msw';

export const getUserApiHandlers = () => [
    rest.get('/api/organisations', (req, res, ctx) => {
        return res(
            ctx.json([
                {
                    uuid: '00000000-0000-0000-0000-000000000000',
                    name: 'Demo GGD1',
                    type: 'regionalGGD',
                },
            ])
        );
    }),
];
