import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { determineIsolationDay, isSymptomatic } from '@/utils/case';
import { formatDateLong } from '@/utils/date';
import type { AllowedVersions } from '..';
import { isolationAdviceV1Options, MessageTemplateTypeV1 } from '@dbco/enum';

export const adviceGivenSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const indexStore = store.getters['index/fragments'];

    let adviceDefaultText = 'Isolatie: [ vul medische gegevens in voor juiste isolatieperiode ]';
    const isolationDay = determineIsolationDay(indexStore);

    if (isolationDay) {
        const isolationDayString = formatDateLong(isolationDay);

        // Compare isSymptomatic with false, should not be null (=unknown/not set)
        if (isSymptomatic(indexStore) === false) {
            adviceDefaultText = `Isolatie asymptomatische index: laatste dag op ${isolationDayString}, tenzij er tussentijds toch nog klachten ontstaan (dan einde isolatie na 24 uur klachtenvrij en laatste dag uiterlijk 10 dagen na eerste ziektedag)`;
        } else if (isSymptomatic(indexStore)) {
            adviceDefaultText = `Isolatie symptomatische index: laatste dag op ${isolationDayString}, als de index 24 uur klachtenvrij is en niet in het ziekenhuis is opgenomen`;
        }
    }

    const fields: Children<AllowedVersions['index']> = [
        generator.div(
            [
                generator
                    .field('communication', 'isolationAdviceGiven')
                    .checkbox('Welke adviezen zijn gegeven?', isolationAdviceV1Options, 1, '', '', 'mb-0'),
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
                        'Noteer alleen adviezen die je daadwerkelijk gegeven hebt. Denk hierbij ook aan adviezen die je alleen voor deze specifieke situatie hebt gegeven.',
                        12
                    )
                    .appendConfig({ maxlength: 5000 }),
            ],
            'w50'
        ),
    ];

    return fields;
};

export const calculatedAdvicesSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const indexUuid = store.getters['index/uuid'];

    return generator.formChapter(
        [
            generator
                .field('contact', 'email')
                .sendEmail(
                    indexUuid,
                    null,
                    MessageTemplateTypeV1.VALUE_personalAdvice,
                    'Verstuur adviezen naar index',
                    'primary'
                )
                .appendConfig({ class: 'mb-0' }),
        ],
        'Adviezen voor de index'
    );
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

export const caseAdvicesTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();
    const chapters = [
        generator.formChapter(adviceGivenSchema(generator), 'Opmerkingen en afgesproken beleid'),
        calculatedAdvicesSchema(generator),
        generator.formChapter(notes(generator), 'Opmerkingen'),
    ];
    return generator.toConfig(chapters);
};
