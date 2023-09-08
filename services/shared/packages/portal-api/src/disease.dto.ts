import type { ValidationResult } from './validation-result.dto';

export enum Entity {
    Dossier = 'dossier',
    Contact = 'contact',
    Event = 'event',
}

export enum VersionStatus {
    Draft = 'draft',
    Published = 'published',
    Archived = 'archived',
}

// Disease
export interface DiseaseListItemDTO {
    id: number;
    code: string;
    name: string;
    currentVersion?: number;
    isActive: boolean;
}

export interface DiseaseCreateUpdateRequestDTO {
    code: string;
    name: string;
}

export interface DiseaseCreateUpdateResponseDTO {
    data: Pick<DiseaseListItemDTO, 'id' | 'code' | 'name'>;
    validationResult?: ValidationResult;
}

// DiseaseModel
export interface DiseaseModelListItemDTO {
    id: number;
    version: number;
    status: VersionStatus;
}

export interface DiseaseModelDetail {
    id: number;
    diseases: Pick<DiseaseListItemDTO, 'id' | 'code' | 'name'>[];
    contactSchema: string;
    dossierSchema: string;
    eventSchema: string;
    sharedDefs?: string;
    status: VersionStatus;
    version: number;
}

export interface DiseaseModelCreateUpdateRequestDTO {
    dossierSchema: string;
    contactSchema: string;
    eventSchema: string;
    sharedDefs?: string;
}

export interface DiseaseModelCreateUpdateResponseDTO {
    data: DiseaseModelDetail;
    validationResult?: ValidationResult;
}

// DiseaseUIModel
export interface DiseaseUIModelListItemDTO {
    id: number;
    version: number;
    status: VersionStatus;
}

export interface DiseaseUIModelDetail {
    id: number;
    diseases: Pick<DiseaseListItemDTO, 'id' | 'code' | 'name'>[];
    contactSchema: string;
    dossierSchema: string;
    eventSchema: string;
    sharedDefs?: string;
    status: VersionStatus;
    version: number;
}

export interface DiseaseUIModelCreateUpdateRequestDTO {
    dossierSchema: string;
    contactSchema: string;
    eventSchema: string;
    sharedDefs?: string;
}

export interface DiseaseUIModelCreateUpdateResponseDTO {
    data: DiseaseModelDetail;
    validationResult?: ValidationResult;
}

// Dossier
export interface DossierDTO {
    data: DossierData;
    links: any;
    form: string;
    validationResult?: ValidationResult;
}

export interface DossierData {
    contacts: AnyObject[];
    diseaseModel: DiseaseModelListItemDTO & { disease: Pick<DiseaseListItemDTO, 'id' | 'code' | 'name'> };
    events: AnyObject[];
    id: number;
    identifier: string;
    // Fragment data
    [key: string]: any;
}

export interface FormDTO {
    dataSchema: AnyObject;
    uiSchema: AnyObject;
    translations: AnyObject;
}
