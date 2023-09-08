import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import type { AllowedVersions } from '..';

import * as caseAdvicesTabV1 from '../../formSchemaV1/tabs/caseAdvicesTab';
import * as caseAdvicesTabV5 from '../../formSchemaV5/tabs/caseAdvicesTab';

export const adviceGivenSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const fields: Children<AllowedVersions['index']> = [
        generator.group(
            [
                generator
                    .field('communication', 'otherAdviceGiven')
                    .textArea(
                        'Gegeven adviezen',
                        'Noteer alleen adviezen die je daadwerkelijk gegeven hebt. Denk hierbij ook aan adviezen die je alleen voor deze specifieke situatie hebt gegeven.'
                    )
                    .appendConfig({ maxlength: 5000 }),
            ],
            'w50'
        ),
    ];

    return fields;
};

export const caseAdvicesTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();
    const chapters = [
        generator.formChapter(
            caseAdvicesTabV5.scientificResearchSchema(generator),
            'Toestemming wetenschappelijk onderzoek'
        ),
        generator.formChapter(adviceGivenSchema(generator), 'Opmerkingen en afgesproken beleid'),
        caseAdvicesTabV1.calculatedAdvicesSchema(generator),
        generator.formChapter(caseAdvicesTabV5.notes(generator), 'Opmerkingen'),
    ];
    return generator.toConfig(chapters);
};
