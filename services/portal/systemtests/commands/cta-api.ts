import faker from '../utils/faker-decorator';
import type { CallToActionRequest } from '@dbco/portal-api/callToAction.dto';
const defaultOrganisationUuid = '00000000-0000-0000-0000-000000000000';
const defaultPermission = 'edit';
const defaultResourceType = 'covid-case';

const generateFakeCallToActionRequest = (): CallToActionRequest => ({
    description: faker.lorem.paragraph(),
    expires_at: faker.date.soon().toISOString(),
    organisation_uuid: defaultOrganisationUuid,
    resource_permission: defaultPermission,
    resource_type: defaultResourceType,
    resource_uuid: faker.string.uuid(),
    subject: faker.lorem.words(),
    role: 'user',
});

export type CreatedCallToAction = {
    assignedUserUuid: string | null;
    createdAt: string;
    createdBy: {
        name: string;
        roles: Array<string>;
        uuid: string;
    };
    description: string;
    expiresAt: string;
    organisationUuid: string | null;
    subject: string;
    resource: {
        uuid: string;
        type: string;
    };
    uuid: string;
};

export const createCallToActionApi = (fakeCallToActionRequest: CallToActionRequest) => {
    return cy.authenticatedAPIRequest<CreatedCallToAction>({
        url: '/api/call-to-actions',
        method: 'PUT',
        body: fakeCallToActionRequest,
    });
};

export const createCallToAction = (resourceUuid: string) => {
    const payload = {
        ...generateFakeCallToActionRequest(),
        ...{ resource_uuid: resourceUuid },
    };

    return cy.createCallToActionApi(payload).its('body').as('createdCallToAction');
};
