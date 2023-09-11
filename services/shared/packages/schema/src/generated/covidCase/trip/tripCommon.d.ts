/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CountryV1 } from '@dbco/enum';
import { TransportationTypeV1 } from '@dbco/enum';

/**
 * TripCommon
 */
export interface TripCommon {
    departureDate?: Date | null;
    returnDate?: Date | null;
    countries?: CountryV1[] | null;
    transportation?: TransportationTypeV1[] | null;
}

export type TripCommonDTO = DTO<TripCommon>;
