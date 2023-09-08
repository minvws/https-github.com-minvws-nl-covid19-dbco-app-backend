import { formRuleMet } from '@/utils/form';
import type { DTO } from '@dbco/schema/dto';
import type { Moment } from '@/components/form/ts/formTypes';
import { FormConditionRule } from '@/components/form/ts/formTypes';
import type { SchemaRule } from '../../schemaType';
import type { AllowedVersions } from '..';
import { StoreType } from '@/store/storeType';

const contextRules: SchemaRule<DTO<AllowedVersions['context']>>[] = [
    {
        title: 'ClearIsSourceWhenHidden',
        watch: 'general.moments',
        callback: (data, [moments]: [Moment[]]) => {
            const days = moments?.map((moment) => new Date(moment.day)) ?? [];
            if (
                days.length === 0 ||
                !formRuleMet(data, FormConditionRule.DateInSourcePeriod, days, StoreType.CONTEXT)
            ) {
                return {
                    'general.isSource': null,
                };
            }

            return {};
        },
    },
];

export default contextRules;
