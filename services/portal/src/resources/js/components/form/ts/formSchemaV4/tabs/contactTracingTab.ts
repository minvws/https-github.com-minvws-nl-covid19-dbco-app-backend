import type { Children } from '@/formSchemas/schemaGenerator';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { yesNoUnknownV1Options } from '@dbco/enum';
import type { AllowedVersions } from '..';
import { isYes } from '../../formOptions';
import * as contactTracingTabV1 from '../../formSchemaV1/tabs/contactTracingTab';
import * as contactTracingTabV2 from '../../formSchemaV2/tabs/contactTracingTab';

export const contactsCountSchema = (generator: SchemaGenerator<AllowedVersions['index']>) => [
    generator.field('contacts', 'estimatedMissingContacts').radioButtonGroup(
        'Ontbraken er nog contacten (cat. 1/2/3) in de tabel?',
        yesNoUnknownV1Options,
        [isYes],
        [
            generator.group(
                [
                    generator
                        .field('contacts', 'estimatedCategory1Contacts')
                        .number(
                            'Hoeveel huisgenoten had de index in totaal?',
                            'Tel de huisgenoten uit de tabel hierboven mee',
                            'col-12'
                        )
                        .appendConfig({
                            min: '0',
                        })
                        .validation(['numeric', 'zeroOrGreater'], 'Categorie 1 contacten'),
                    generator
                        .field('contacts', 'estimatedCategory2Contacts')
                        .number(
                            'Hoeveel nauwe contacten had de index in totaal?',
                            'Tel de nauwe contacten uit de tabel hierboven mee',
                            'col-12'
                        )
                        .appendConfig({
                            min: '0',
                        })
                        .validation(['numeric', 'zeroOrGreater'], 'Categorie 2 contacten'),
                    generator
                        .field('contacts', 'estimatedCategory3Contacts')
                        .number(
                            'Hoeveel overige contacten had de index in totaal?',
                            'Tel de overige contacten uit de tabel hierboven mee',
                            'col-12'
                        )
                        .appendConfig({
                            min: '0',
                        })
                        .validation(['numeric', 'zeroOrGreater'], 'Categorie 3 contacten'),
                ],
                'col-6'
            ),
        ]
    ),
];

export const contactTracingTabSchema = <TModel extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TModel>();

    const chapters: Children<TModel> = [contactTracingTabV1.formInfoSchema(generator)];

    chapters.push(generator.formChapter(contactTracingTabV2.extensiveContactTracingSchema(generator)));

    return generator.toConfig([
        ...chapters,
        generator.formChapter(contactTracingTabV2.contactsSchema(generator), 'Contacten binnen besmettelijke periode'),
        generator.formChapter(contactsCountSchema(generator)),
        generator.formChapter(
            contactTracingTabV1.contactsContagiousPeriodSchema(generator),
            'Contexten binnen besmettelijke periode'
        ),
        generator.formChapter(
            contactTracingTabV1.groupTransportSchema(generator),
            'Vliegreis of groepsvervoer met gereserveerde stoelen'
        ),
        generator.formChapter(contactTracingTabV1.pairCaseSchema(generator), 'GGD Contact'),
    ]);
};
