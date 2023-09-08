import type { CallQueue } from '../../sectionManagementTypes';
import { placeApi } from '@dbco/portal-api';

/**
 * Make updatePlaceSections request with changeLabelQueue entries.
 *
 * @param placeUuid uuid of place the sections belong to.
 * @param changeLabelQueue queue for label change api calls to make if changes are saved.
 * @returns updatePlaceSections request.
 */
const makeChangeLabelRequest = (placeUuid: string, changeLabelQueue: CallQueue['changeLabelQueue']) =>
    placeApi.updatePlaceSections(placeUuid, changeLabelQueue);

export default makeChangeLabelRequest;
