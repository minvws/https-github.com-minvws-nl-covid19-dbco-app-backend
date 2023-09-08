import type { DTO } from '@dbco/schema/dto';
import type { AllowedVersions } from '..';
import type { ExtensiveContactTracingReasonV1 } from '@dbco/enum';
import { BcoTypeV1 } from '@dbco/enum';
import type { SchemaRule } from '../../schemaType';
import * as indexRulesV1 from '../../formSchemaV1/rules/index';

const isExtensiveContactTracing: SchemaRule<DTO<AllowedVersions['index']>> = {
    title: 'IsExtensiveContactTracing',
    watch: 'extensiveContactTracing.reasons',
    callback: (data, [newReasons]: [ExtensiveContactTracingReasonV1[]]) => {
        if (!data.extensiveContactTracing.receivesExtensiveContactTracing && newReasons?.length > 0) {
            return {
                'extensiveContactTracing.receivesExtensiveContactTracing': BcoTypeV1.VALUE_extensive,
            };
        }

        return {};
    },
};

const indexRules: SchemaRule<DTO<AllowedVersions['index']>>[] = [
    // V1
    indexRulesV1.dateOfSymptomOnset,
    indexRulesV1.dateOfTestAndSymptoms,
    indexRulesV1.hasSymptoms,
    indexRulesV1.hasSymptomsButNoWasSymptomaticAtTimeOfCall,
    indexRulesV1.isStillSymptomatic,

    // V2
    isExtensiveContactTracing,
];

export default indexRules;
