import type { DTO } from '@dbco/schema/dto';
import type { AllowedVersions } from '..';
import { ContactCategoryV1, InformedByV1 } from '@dbco/enum';
import type { SchemaRule } from '../../schemaType';

const taskRules: SchemaRule<DTO<AllowedVersions['task']>>[] = [
    {
        title: 'Category3a3b',
        watch: 'general.category',
        callback: (data, [category]: any) => {
            if (
                [ContactCategoryV1.VALUE_3a, ContactCategoryV1.VALUE_3b].includes(category) &&
                data.inform.informedBy === null
            ) {
                return {
                    'inform.informedBy': InformedByV1.VALUE_index,
                };
            }

            return {};
        },
    },
];

export default taskRules;
