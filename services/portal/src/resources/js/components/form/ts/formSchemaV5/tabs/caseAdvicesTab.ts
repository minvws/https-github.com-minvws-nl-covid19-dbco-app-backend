import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { yesNoUnknownV1Options } from '@dbco/enum';
import type { AllowedVersions } from '..';

import * as caseAdvicesTabV1 from '../../formSchemaV1/tabs/caseAdvicesTab';
import * as caseAdvicesTabV3 from '../../formSchemaV3/tabs/caseAdvicesTab';

export const notes = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    return [
        generator.group([
            generator
                .field('communication', 'particularities')
                .textArea(
                    'Opmerkingen en bijzonderheden over het BCO-gesprek',
                    'Bijvoorbeeld: index werkt wel / niet goed mee, taalbarrière, vermoeden van een onveilige thuissituatie, etc.',
                    12
                )
                .appendConfig({ maxlength: 5000 }),
            generator
                .field('communication', 'remarksRivm')
                .textArea(
                    'Aantekeningen voor LCI / EPI',
                    'Maak alleen een aantekening wanneer je vermoedt dat er (mogelijk) sprake is van een VOI/VOC',
                    12
                )
                .appendConfig({ maxlength: 5000 }),
        ]),
    ];
};

export const scientificResearchSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator
        .field('communication', 'scientificResearchConsent')
        .radioButtonGroup('Mag de GGD de index benaderen voor toekomstig onderzoek?', yesNoUnknownV1Options, []),
];

export const caseAdvicesTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();
    const chapters = [
        generator.formChapter(scientificResearchSchema(generator), 'Toestemming wetenschappelijk onderzoek'),
        generator.formChapter(caseAdvicesTabV3.adviceGivenSchema(generator), 'Opmerkingen en afgesproken beleid'),
        caseAdvicesTabV1.calculatedAdvicesSchema(generator),
        generator.formChapter(notes(generator), 'Opmerkingen'),
    ];
    return generator.toConfig(chapters);
};
