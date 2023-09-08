import { getAxiosInstance } from '../defaults';
import type {
    DiseaseCreateUpdateRequestDTO,
    DiseaseCreateUpdateResponseDTO,
    DiseaseListItemDTO,
    DiseaseModelListItemDTO,
    DiseaseModelCreateUpdateRequestDTO,
    DiseaseModelCreateUpdateResponseDTO,
    DiseaseUIModelCreateUpdateRequestDTO,
    DiseaseUIModelListItemDTO,
    DiseaseUIModelCreateUpdateResponseDTO,
    DossierDTO,
    Entity,
    FormDTO,
} from '../disease.dto';

// Disease
export const listDiseases = () =>
    getAxiosInstance()
        .get<DiseaseListItemDTO[]>('/api/diseases')
        .then((res) => res.data);

export const createDisease = (data: DiseaseCreateUpdateRequestDTO) =>
    getAxiosInstance()
        .post<DiseaseCreateUpdateResponseDTO>('/api/diseases', data)
        .then((res) => res.data);

export const deleteDisease = (id: number) =>
    getAxiosInstance()
        .delete(`/api/diseases/${id}`)
        .then((res) => res.data);

export const updateDisease = (id: number, data: DiseaseCreateUpdateRequestDTO) =>
    getAxiosInstance()
        .put<DiseaseCreateUpdateResponseDTO>(`/api/diseases/${id}`, data)
        .then((res) => res.data);

// DiseaseModel
export const listDiseaseModels = (diseaseId: number) =>
    getAxiosInstance()
        .get<DiseaseModelListItemDTO[]>(`/api/diseases/${diseaseId}/models`)
        .then((res) => res.data);

export const createDiseaseModel = (diseaseId: number, data: DiseaseModelCreateUpdateRequestDTO) =>
    getAxiosInstance()
        .post<DiseaseModelCreateUpdateResponseDTO>(`/api/diseases/${diseaseId}/models`, data)
        .then((res) => res.data);

export const getDiseaseModel = (id: number) =>
    getAxiosInstance()
        .get<DiseaseModelCreateUpdateResponseDTO>(`/api/disease-models/${id}`)
        .then((res) => res.data);

export const updateDiseaseModel = (id: number, data: DiseaseModelCreateUpdateRequestDTO) =>
    getAxiosInstance()
        .put<DiseaseModelCreateUpdateResponseDTO>(`/api/disease-models/${id}`, data)
        .then((res) => res.data);

export const deleteDiseaseModel = (id: number) =>
    getAxiosInstance()
        .delete(`/api/disease-models/${id}`)
        .then((res) => res.data);

export const archiveDiseaseModel = (id: number) =>
    getAxiosInstance()
        .patch(`/api/disease-models/${id}/archive`)
        .then((res) => res.data);

export const publishDiseaseModel = (id: number) =>
    getAxiosInstance()
        .patch(`/api/disease-models/${id}/publish`)
        .then((res) => res.data);

export const cloneDiseaseModel = (id: number) =>
    getAxiosInstance()
        .patch(`/api/disease-models/${id}/clone`)
        .then((res) => res.data);

// DiseaseUIModel
export const listDiseaseUIModels = (diseaseModelId: number) =>
    getAxiosInstance()
        .get<DiseaseUIModelListItemDTO[]>(`/api/disease-models/${diseaseModelId}/uis`)
        .then((res) => res.data);

export const createDiseaseUIModel = (diseaseModelId: number, data: DiseaseUIModelCreateUpdateRequestDTO) =>
    getAxiosInstance()
        .post<DiseaseUIModelCreateUpdateResponseDTO>(`/api/disease-models/${diseaseModelId}/uis`, data)
        .then((res) => res.data);

export const getDiseaseUIModel = (id: number) =>
    getAxiosInstance()
        .get<DiseaseUIModelCreateUpdateResponseDTO>(`/api/disease-model-uis/${id}`)
        .then((res) => res.data);

export const updateDiseaseUIModel = (id: number, data: DiseaseUIModelCreateUpdateRequestDTO) =>
    getAxiosInstance()
        .put<DiseaseUIModelCreateUpdateResponseDTO>(`/api/disease-model-uis/${id}`, data)
        .then((res) => res.data);

export const deleteDiseaseUIModel = (id: number) =>
    getAxiosInstance()
        .delete(`/api/disease-model-uis/${id}`)
        .then((res) => res.data);

export const archiveDiseaseUIModel = (id: number) =>
    getAxiosInstance()
        .patch(`/api/disease-model-uis/${id}/archive`)
        .then((res) => res.data);

export const publishDiseaseUIModel = (id: number) =>
    getAxiosInstance()
        .patch(`/api/disease-model-uis/${id}/publish`)
        .then((res) => res.data);

export const cloneDiseaseUIModel = (id: number) =>
    getAxiosInstance()
        .patch(`/api/disease-model-uis/${id}/clone`)
        .then((res) => res.data);

// Dossier
export const createDossier = (diseaseId: number, version: number | 'current' | 'draft', data: AnyObject) =>
    getAxiosInstance()
        .post<DossierDTO>(`/api/diseases/${diseaseId}/models/${version}/dossiers`, data)
        .then((res) => res.data);

export const getForm = (diseaseId: number, version: number | 'current' | 'draft', entity: Entity) =>
    getAxiosInstance()
        .get<FormDTO>(`/api/diseases/${diseaseId}/models/${version}/forms/${entity}`)
        .then((res) => res.data);

export const getDossier = (id: number) =>
    getAxiosInstance()
        .get<DossierDTO>(`/api/dossiers/${id}`)
        .then((res) => res.data);

export const updateDossier = (id: number, data: AnyObject) =>
    getAxiosInstance()
        .put<DossierDTO>(`/api/dossiers/${id}`, data)
        .then((res) => res.data);
