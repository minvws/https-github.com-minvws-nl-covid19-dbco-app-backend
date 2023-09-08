import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { formatDate, parseDate } from '@/utils/date';
import type { AllowedVersions } from '..';
import { add, sub } from 'date-fns';
import { countryV1Options, transportationTypeV1Options, yesNoUnknownV1Options } from '@dbco/enum';
import { isYes } from '../../formOptions';
import type { DTO } from '@dbco/schema/dto';
import * as sourceTracingTabV1 from '../../formSchemaV1/tabs/sourceTracingTab';

export const abroadSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => {
    const created_at = parseDate(store.getters['index/meta'].createdAt);
    // Dynamic label which is based on dateOfSymptomOnset

    const groupLabel = () => {
        const fragments: DTO<AllowedVersions['index']> = store.getters['index/fragments'];
        const { dateOfSymptomOnset } = fragments.test;

        if (dateOfSymptomOnset) {
            const minDate = sub(parseDate(dateOfSymptomOnset, 'yyyy-MM-dd'), { days: 14 });
            const maxDate = parseDate(dateOfSymptomOnset, 'yyyy-MM-dd');

            return `Index is in de 14 dagen voor EZD (${formatDate(minDate, 'd MMM')} - ${formatDate(
                maxDate,
                'd MMM'
            )}) in een risicoland geweest (zie werkinstructie)`;
        }

        return `Index is in de 14 dagen voor EZD in een risicoland geweest (zie werkinstructie)`;
    };

    return [
        generator.field('abroad', 'wasAbroad').radioButtonGroup(
            groupLabel,
            yesNoUnknownV1Options,
            [isYes],
            [
                generator.field('abroad', 'trips').repeatableGroup(
                    '+ Nog een verblijf in het buitenland toevoegen',
                    [
                        generator.group(
                            [
                                SchemaGenerator.orphanField('departureDate')
                                    .datePicker('Datum vertrek')
                                    .appendConfig({
                                        min: formatDate(sub(created_at, { years: 1 }), 'yyyy-MM-dd'),
                                        max: formatDate(created_at, 'yyyy-MM-dd'),
                                        validation: `optional|before:${formatDate(
                                            add(created_at, { days: 1 }),
                                            'yyyy-MM-dd'
                                        )}|after:${formatDate(sub(created_at, { years: 1, days: 1 }), 'yyyy-MM-dd')}`,
                                    }),
                                SchemaGenerator.orphanField('returnDate').datePicker('Datum terugkeer'),
                            ],
                            'container mb-3'
                        ),
                        generator.group(
                            [
                                SchemaGenerator.orphanField('countries').chips(
                                    'Welke land(en)?',
                                    'Kies land(en)',
                                    countryV1Options
                                ),
                                SchemaGenerator.orphanField('transportation').chips(
                                    'Met welke vervoersmiddel(en)?',
                                    'Kies vervoersmiddel(en)',
                                    transportationTypeV1Options
                                ),
                            ],
                            'container'
                        ),
                    ],
                    1
                ),
            ],
            '',
            'd-flex flex-column'
        ),
    ];
};

export const sourceTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    return generator.toConfig([
        sourceTracingTabV1.formInfoSchema(generator),
        generator.formChapter(sourceTracingTabV1.sourceSchema(generator)),
        generator.formChapter(sourceTracingTabV1.contextsSchema(generator), 'Broncontexten'),
        generator.formChapter(abroadSchema(generator), 'Buitenland'),
    ]);
};
