export enum BsnLookupError {
    NO_FULL_MATCH = 'Given BSN does not match any from lookup',
    NO_MATCHING_RESULTS = 'No matching results found',
    TOO_MANY_RESULTS = 'Too many matching results found',
    SERVICE_UNAVAILABLE = 'Service unavailable',
    NOT_FOUND = 'Not found',
}

export type BsnLookupRequest = {
    dateOfBirth: string;
    postalCode: string;
    houseNumber: string;
    houseNumberSuffix?: string;
    lastThreeDigits?: string;
    bsn?: string;
};

export type BsnLookupResponse =
    | {
          guid: string;
          censoredBsn: string;
          letters: string;
      }
    | {
          error?: BsnLookupError;
      };
