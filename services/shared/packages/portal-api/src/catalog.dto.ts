export enum CatalogCategory {
    ENTITY = 'entity',
    ENUM = 'enum',
    FRAGMENT = 'fragment',
    MODEL = 'model',
}

export enum CatalogPurposeTranslations {
    epidemiologicalSurveillance = 'Epidemiologische surveillance',
    qualityOfCare = 'Kwaliteit van zorg',
    administrativeAdvice = 'Bestuurlijke advisering',
    operationalAdjustment = 'Operationele bijsturing',
    scientificResearch = 'Wetenschappelijk onderzoek',
}

export const CatalogPurposes = [
    'epidemiologicalSurveillance',
    'qualityOfCare',
    'administrativeAdvice',
    'operationalAdjustment',
    'scientificResearch',
];

export enum Filters {
    main = 'main',
    all = 'all',
}

export interface CatalogElement {
    category: CatalogCategory;
    type: string;
    class: string;
    version: number;
    name: string;
    label: string;
    shortDescription: string | null;
    _links: {
        self: string;
    };
}

export interface CatalogListResponse {
    elements: CatalogElement[];
}

export interface CatalogFieldType {
    class?: string;
    name?: string;
    diffToVersion?: number;
    type: string;
    version?: number;
    elementType?: CatalogFieldType;
}

export interface CatalogDetailConditions {
    all?: string;
    input?: string;
    load?: string;
    output?: string;
    store?: string;
}

export interface CatalogPurposeDetail {
    purpose: {
        id: string;
        label: string;
    };
    subPurpose: {
        id: string;
        label: string;
    };
}

export interface CatalogPurposeSpecification {
    purposes: CatalogPurposeDetail[];
    remark?: string;
}

export interface CatalogDetailField {
    name: string;
    label: string | null;
    shortDescription: string | null;
    description: string | null;
    condition?: CatalogDetailConditions;
    diffResult?: string;
    type: CatalogFieldType;
    purposeSpecification: CatalogPurposeSpecification;
}

export interface CatalogDiffableVersion {
    version: number;
    _links: {
        diff: string;
        self: string;
    };
}

export interface CatalogDetailResponse {
    _links: {
        self: string;
    };
    class: string;
    currentVersion: {
        version: number;
        _links: {
            self: string;
        };
    };
    description: string | null;
    diffableVersions: CatalogDiffableVersion[];
    diffToVersion?: number;
    fields?: CatalogDetailField[];
    label: string;
    maxVersion: {
        version: number;
        _links: {
            self: string;
        };
    };
    minVersion: {
        version: number;
        _links: {
            self: string;
        };
    };
    name: string;
    options?: { label: string; value: string }[];
    shortDescription: string | null;
    category: CatalogCategory;
    version: number;
}
