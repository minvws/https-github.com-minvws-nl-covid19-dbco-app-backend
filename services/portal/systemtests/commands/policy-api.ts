import type { PolicyVersion } from '@dbco/portal-api/admin.dto';
import { PolicyVersionStatusV1 } from '@dbco/enum';
import faker from '../utils/faker-decorator';
import type { User } from './login';

export function givenPolicyPayload(partial?: Partial<PolicyVersion>): PolicyVersion {
    return {
        uuid: faker.string.uuid(),
        name: faker.lorem.words(3),
        startDate: faker.date.soon(),
        status: PolicyVersionStatusV1.VALUE_draft,
        ...partial,
    };
}

export type CreatedPolicy = {
    body: {
        uuid: string;
        name: string;
        startDate: Date;
    };
};

const referrers = {
    admin: '/beheren',
} as const;

export const createPolicyApi = (data: PolicyVersion = givenPolicyPayload()) => {
    return cy
        .get<User>('@loggedInUser')
        .then((user) => {
            return referrers[user] ?? '/beheren';
        })
        .then((referer) => {
            return cy.authenticatedAPIRequest<{
                data: CreatedPolicy;
            }>({
                url: 'api/admin/policy-version',
                method: 'POST',
                headers: {
                    Referer: referer,
                },
                body: data,
            });
        })
        .then((response) => response.body.data);
};
