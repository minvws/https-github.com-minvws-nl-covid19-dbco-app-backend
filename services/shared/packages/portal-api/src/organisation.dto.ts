import type { OrganisationV1DTO } from '@dbco/schema/organisation/organisationV1';

export type Organisation = Pick<OrganisationV1DTO, 'name' | 'uuid'>;
