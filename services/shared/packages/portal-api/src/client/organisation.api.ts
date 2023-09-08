import { getAxiosInstance } from '../defaults';
import type { Organisation } from '@dbco/portal-api/organisation.dto';

// Misc organisations
export const getOrganisations = () =>
    getAxiosInstance()
        .get<Organisation[]>(`/api/organisations`)
        .then((res) => res.data);
