import type {
    CalendarItem,
    CalendarItemConfig,
    CalendarItemConfigDateOperation,
    CalendarItemConfigStrategy,
    CalendarView,
    PolicyGuideline,
    PolicyVersion,
    RiskProfile,
} from '@dbco/portal-api/admin.dto';
import type { AxiosResponse } from 'axios';
import { getAxiosInstance } from '../defaults';
import { type PolicyPersonTypeV1 } from '@dbco/enum';

export const createPolicyVersion = async (
    payload: Pick<PolicyVersion, 'name' | 'startDate'> | undefined
): Promise<PolicyVersion> => {
    const { data }: AxiosResponse<PolicyVersion> = await getAxiosInstance().post('api/admin/policy-version', payload);
    return data;
};

export const getPolicyVersions = async (): Promise<PolicyVersion[]> => {
    const { data }: AxiosResponse<PolicyVersion[]> = await getAxiosInstance().get(`api/admin/policy-version`);
    return data;
};

export const getPolicyVersion = async (versionUuid: string): Promise<PolicyVersion> => {
    const { data }: AxiosResponse<PolicyVersion> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}`
    );
    return data;
};

export const updatePolicyVersion = async (
    versionUuid: string,
    payload: Partial<PolicyVersion>
): Promise<PolicyVersion> => {
    const { data }: AxiosResponse<PolicyVersion> = await getAxiosInstance().put(
        `api/admin/policy-version/${versionUuid}`,
        payload
    );
    return data;
};
export const getPolicyGuidelines = async (versionUuid: string): Promise<PolicyGuideline[]> => {
    const { data }: AxiosResponse<PolicyGuideline[]> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}/policy-guideline`
    );
    return data;
};

export const getPolicyGuideline = async (
    versionUuid: string,
    policyGuidelineUuid: string
): Promise<PolicyGuideline> => {
    const { data }: AxiosResponse<PolicyGuideline> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}/policy-guideline/${policyGuidelineUuid}`
    );
    return data;
};

export const updatePolicyGuideline = async (payload: Partial<PolicyGuideline>): Promise<PolicyGuideline> => {
    const { policyVersionUuid, uuid } = payload;
    const { data }: AxiosResponse<PolicyGuideline> = await getAxiosInstance().put(
        `api/admin/policy-version/${policyVersionUuid}/policy-guideline/${uuid}`,
        payload
    );
    return data;
};

export const getRiskProfiles = async (versionUuid: string, personType: PolicyPersonTypeV1): Promise<RiskProfile[]> => {
    const { data }: AxiosResponse<RiskProfile[]> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}/risk-profile?filter[person]=${personType}`
    );
    return data;
};

export const updateRiskProfile = async (
    versionUuid: string,
    policyGuidelineUuid: string,
    payload: Partial<RiskProfile>
) => {
    const { data }: AxiosResponse<RiskProfile> = await getAxiosInstance().put(
        `/api/admin/policy-version/${versionUuid}/risk-profile/${policyGuidelineUuid}`,
        payload
    );
    return data;
};

export const getCalendarItems = async (versionUuid: string): Promise<CalendarItem[]> => {
    const { data }: AxiosResponse<CalendarItem[]> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}/calendar-item`
    );
    return data;
};

export const createCalendarItem = async (
    versionUuid: string,
    payload: Partial<CalendarItem> | undefined
): Promise<CalendarItem> => {
    const { data }: AxiosResponse<CalendarItem> = await getAxiosInstance().post(
        `api/admin/policy-version/${versionUuid}/calendar-item`,
        payload
    );
    return data;
};

export const updateCalendarItem = async (versionUuid: string, itemUuid: string, payload: Partial<CalendarItem>) => {
    const { data }: AxiosResponse<CalendarItem> = await getAxiosInstance().put(
        `/api/admin/policy-version/${versionUuid}/calendar-item/${itemUuid}`,
        payload
    );
    return data;
};

export const deleteCalendarItem = async (versionUuid: string, itemUuid: string) => {
    const { data }: AxiosResponse<CalendarItem> = await getAxiosInstance().delete(
        `/api/admin/policy-version/${versionUuid}/calendar-item/${itemUuid}`
    );
    return data;
};

export const getCalendarItemConfigs = async (
    guidelineUuid: string,
    versionUuid: string
): Promise<CalendarItemConfig[]> => {
    const { data }: AxiosResponse<CalendarItemConfig[]> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}/policy-guideline/${guidelineUuid}/calendar-item-config`
    );
    return data;
};

export const getCalendarItemConfig = async (
    configUuid: string,
    guidelineUuid: string,
    versionUuid: string
): Promise<CalendarItemConfig> => {
    const { data }: AxiosResponse<CalendarItemConfig> = await getAxiosInstance().get(
        `api/admin/policy-version/${versionUuid}/policy-guideline/${guidelineUuid}/calendar-item-config/${configUuid}`
    );
    return data;
};

export const updateCalendarItemConfig = async (
    configUuid: string,
    guidelineUuid: string,
    versionUuid: string,
    payload: Pick<CalendarItemConfig, 'isHidden'>
): Promise<CalendarItemConfig> => {
    const { data }: AxiosResponse<CalendarItemConfig> = await getAxiosInstance().put(
        `api/admin/policy-version/${versionUuid}/policy-guideline/${guidelineUuid}/calendar-item-config/${configUuid}`,
        payload
    );
    return data;
};

export const updateCalendarItemConfigStrategy = async (
    configUuid: string,
    guidelineUuid: string,
    strategyUuid: string,
    versionUuid: string,
    payload: Pick<CalendarItemConfigStrategy, 'strategyType'>
): Promise<CalendarItemConfig> => {
    const { data }: AxiosResponse<CalendarItemConfig> = await getAxiosInstance().put(
        `api/admin/policy-version/${versionUuid}/policy-guideline/${guidelineUuid}/calendar-item-config/${configUuid}/calendar-item-config-strategy/${strategyUuid}`,
        payload
    );
    return data;
};

export const updateCalendarItemConfigOperation = async (
    configUuid: string,
    guidelineUuid: string,
    operationUuid: string,
    strategyUuid: string,
    versionUuid: string,
    payload: CalendarItemConfigDateOperation
): Promise<CalendarItemConfig> => {
    const { data }: AxiosResponse<CalendarItemConfig> = await getAxiosInstance().put(
        `api/admin/policy-version/${versionUuid}/policy-guideline/${guidelineUuid}/calendar-item-config/${configUuid}/calendar-item-config-strategy/${strategyUuid}/date-operation/${operationUuid}`,
        payload
    );
    return data;
};

export const getCalendarView = async (versionUuid: string, viewUuid: string) => {
    const { data }: AxiosResponse<CalendarView> = await getAxiosInstance().get(
        `/api/admin/policy-version/${versionUuid}/calendar-view/${viewUuid}`
    );
    return data;
};

export const getCalendarViews = async (versionUuid: string) => {
    const { data }: AxiosResponse<CalendarView[]> = await getAxiosInstance().get(
        `/api/admin/policy-version/${versionUuid}/calendar-view`
    );
    return data;
};

export const updateCalendarView = async (payload: Partial<CalendarView>): Promise<CalendarView> => {
    const { policyVersionUuid, uuid } = payload;
    const { data }: AxiosResponse<CalendarView> = await getAxiosInstance().put(
        `api/admin/policy-version/${policyVersionUuid}/calendar-view/${uuid}`,
        payload
    );
    return data;
};
