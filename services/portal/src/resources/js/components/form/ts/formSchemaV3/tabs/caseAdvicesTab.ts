import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { determineIsolationDay, getIsolationAdviceSymptomatic, isSymptomatic } from '@/utils/case';
import { formatDateLong } from '@/utils/date';
import type { DTO } from '@dbco/schema/dto';
import { isolationAdviceV2Options } from '@dbco/enum';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';
import type { AllowedVersions } from '..';
import * as caseAdvicesTabV1 from '../../formSchemaV1/tabs/caseAdvicesTab';

export const adviceGivenSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const indexStore: DTO<AllowedVersions['index']> = store.getters['index/fragments'];

    let adviceDefaultText = 'Isolatie: [ vul medische gegevens in voor juiste isolatieperiode ]';

    const isolationDay = determineIsolationDay(indexStore);
    const firstDayOfSymptomsOnSet = indexStore.test.dateOfSymptomOnset;
    if (isolationDay && firstDayOfSymptomsOnSet) {
        // Compare isSymptomatic with false, should not be null (=unknown/not set)
        if (isSymptomatic(indexStore) === false) {
            const isolationDayString = formatDateLong(isolationDay);
            adviceDefaultText = `Isolatie asymptomatische index: laatste dag op ${isolationDayString} Alsnog klachten? Dan thuis blijven t/m 10 dagen na EZD. Index mag eventueel naar buiten als er 5 dagen voorbij zijn gegaan sinds EZD en als de index 24 uur klachtenvrij is.`;
        } else if (isSymptomatic(indexStore)) {
            adviceDefaultText = getIsolationAdviceSymptomatic(new Date(firstDayOfSymptomsOnSet));
        }
    }

    const fields: Children<AllowedVersions['index']> = [
        generator.div(
            [
                generator
                    .field('communication', 'isolationAdviceGiven')
                    .checkbox('Welke adviezen zijn gegeven?', isolationAdviceV2Options, 1, '', '', 'mb-0'),
            ],
            'row'
        ),
        generator.group(
            [
                generator
                    .field('communication', 'conditionalAdviceGiven')
                    .inputCheckbox('', adviceDefaultText, 'textarea', 12),
            ],
            'mb-3'
        ),
        generator.group(
            [
                generator
                    .field('communication', 'otherAdviceGiven')
                    .textArea(
                        'Andere gegeven adviezen',
                        'Noteer alleen adviezen die je daadwerkelijk gegeven hebt. Denk hierbij ook aan adviezen die je alleen voor deze specifieke situatie hebt gegeven.'
                    )
                    .appendConfig({ maxlength: 5000 }),
            ],
            'w50'
        ),
    ];

    return fields;
};

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
        ]),
    ];
};

export const caseAdvicesTabSchema = <TModel extends CovidCaseV3 | CovidCaseV4>() => {
    const generator = new SchemaGenerator<TModel>();
    const chapters = [
        generator.formChapter(adviceGivenSchema(generator), 'Opmerkingen en afgesproken beleid'),
        caseAdvicesTabV1.calculatedAdvicesSchema(generator),
        generator.formChapter(notes(generator), 'Opmerkingen'),
    ];
    return generator.toConfig(chapters);
};
