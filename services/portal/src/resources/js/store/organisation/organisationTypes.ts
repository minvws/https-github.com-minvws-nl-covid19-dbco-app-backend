/* istanbul ignore file */
import type { Organisation } from '@/components/form/ts/formTypes';

export interface OrganisationStoreState {
    all: Partial<Organisation>[];
    current: Partial<Organisation> | undefined;
    currentFromAddressSearch: string | undefined;
    error: string;
}
