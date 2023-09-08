import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';
import { placeApi } from '@dbco/portal-api';

const checkForDuplicates = async (place: Partial<PlaceDTO | LocationDTO>): Promise<Partial<PlaceDTO>[]> => {
    let duplicates: Partial<PlaceDTO>[] = [];
    if (!place.address) return duplicates;
    const { postalCode, houseNumber, houseNumberSuffix } = place.address;
    const id = 'uuid' in place ? place.uuid : '';
    if (postalCode?.length && houseNumber?.length) {
        await placeApi.search(postalCode).then((data) => {
            if (data.places.length > 0) {
                duplicates = data.places.filter(
                    (placeFromData: Partial<PlaceDTO>) =>
                        placeFromData.uuid !== id && // filter current place, when editing
                        placeFromData.address?.postalCode === postalCode &&
                        placeFromData.address.houseNumber === houseNumber &&
                        (placeFromData.address.houseNumberSuffix === houseNumberSuffix ||
                            (!placeFromData.address.houseNumberSuffix?.length && !houseNumberSuffix?.length))
                );
            }
        });
    }
    return duplicates;
};

export default checkForDuplicates;
