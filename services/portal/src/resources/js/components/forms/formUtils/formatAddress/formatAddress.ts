import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';
import { formatPostalCode } from '@/utils/formatPostalCode';

/**
 * Formats place address.
 * @param place place to format.
 * @returns place with formatted address.
 */
const formatAddress = (place: Partial<PlaceDTO | LocationDTO>): Partial<PlaceDTO | LocationDTO> => {
    if (!place.address) return place;
    place.address.postalCode = formatPostalCode(place.address.postalCode) || place.address.postalCode;
    place.address.houseNumberSuffix = place.address.houseNumberSuffix || '';

    // Possibly null: FormAddressLookup sets these to null for validation.
    place.address.street = place.address.street || '';
    place.address.town = place.address.town || '';

    const { street, houseNumber, houseNumberSuffix, postalCode, town } = place.address;
    place.addressLabel = `${street} ${houseNumber} ${houseNumberSuffix}`.trim() + `, ${postalCode} ${town}`.trim();
    return place;
};

export default formatAddress;
