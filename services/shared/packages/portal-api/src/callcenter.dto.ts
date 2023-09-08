export interface CallcenterSearchRequest {
    dateOfBirth: string;
    lastThreeBsnDigits?: string;
    postalCode?: string;
    houseNumber?: string;
    houseNumberSuffix?: string;
    lastname?: string;
    phone?: string;
}

export interface CallcenterSearchResult {
    uuid: string;
    token: string;
    caseType: 'index' | 'contact';
    testDate?: string;
    dateOfLastExposure?: string;
    personalDetails: PersonalDetails[];
}

export interface PersonalDetails {
    key: 'dateOfBirth' | 'lastThreeBsnDigits' | 'address' | 'lastname' | 'phone';
    value: string;
    isMatch: boolean;
}
