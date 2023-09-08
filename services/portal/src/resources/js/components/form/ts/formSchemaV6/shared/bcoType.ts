import type { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { BcoTypeV1 } from '@dbco/enum';
import type { AllowedVersions } from '..';

export const standardBCOInfoSchema = (generator: SchemaGenerator<AllowedVersions['index']>) =>
    generator
        .slot(
            [
                generator.info(
                    'Je vult de verkorte vragenlijst in. Mis je een vraag? Kies dan voor het uitvoeren van een uitgebreid BCO.',
                    true,
                    12,
                    'info',
                    'px-0 mb-3'
                ),
            ],
            [
                {
                    prop: 'extensiveContactTracing.receivesExtensiveContactTracing',
                    values: [BcoTypeV1.VALUE_standard],
                },
            ],
            undefined,
            undefined,
            'mt-4'
        )
        .appendConfig({ 'outer-class': '' });
