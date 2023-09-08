import type {
    CallToActionHistoryItem,
    CallToActionRequest,
    CallToActionResponse,
} from '@dbco/portal-api/callToAction.dto';
import { Role } from '@dbco/portal-api/user';
import { fakerjs } from '@/utils/test';
import { ChoreResourceTypeV1, ResourcePermissionV1, CallToActionEventV1 } from '@dbco/enum';

const defaultResourceType = ChoreResourceTypeV1.VALUE_covid_case;
const defaultOrganisationUuid = '00000000-0000-0000-0000-000000000000';

export const generateFakeCallToActionHistoryItem = (
    datetime = fakerjs.date.recent().toISOString(),
    callToActionEvent = CallToActionEventV1.VALUE_picked_up
): CallToActionHistoryItem => ({
    datetime,
    callToActionEvent,
    note: fakerjs.lorem.paragraph(),
    user: {
        name: fakerjs.person.fullName(),
        roles: [Role.user],
        uuid: fakerjs.string.uuid(),
    },
});

export const generateFakeCallToActionResponse = (assigned = false): CallToActionResponse => ({
    assignedUserUuid: assigned ? fakerjs.string.uuid() : null,
    createdAt: fakerjs.date.recent().toISOString(),
    createdBy: null,
    description: fakerjs.lorem.paragraph(),
    expiresAt: fakerjs.date.soon().toISOString(),
    resource: {
        type: defaultResourceType,
        uuid: fakerjs.string.uuid(),
    },
    subject: fakerjs.lorem.sentence(),
    uuid: fakerjs.string.uuid(),
});

export const generateFakeCallToActionRequest = (): CallToActionRequest => ({
    subject: fakerjs.lorem.words(),
    organisation_uuid: defaultOrganisationUuid,
    resource_uuid: fakerjs.string.uuid(),
    resource_type: defaultResourceType,
    resource_permission: ResourcePermissionV1.VALUE_edit,
    expires_at: fakerjs.date.soon().toISOString(),
    description: fakerjs.lorem.paragraph(),
    role: Role.user,
});

export const fakeAssignedCTA: CallToActionResponse = generateFakeCallToActionResponse(true);

export const fakeCallToAction: CallToActionResponse = generateFakeCallToActionResponse();

export const fakeCallToActionHistoryItem: CallToActionHistoryItem = generateFakeCallToActionHistoryItem();
