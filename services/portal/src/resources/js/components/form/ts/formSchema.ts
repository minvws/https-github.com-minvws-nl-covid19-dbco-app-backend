import env from '@/env';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import { formatDate } from '@/utils/date';
import { generateSafeHtml } from '@/utils/safeHtml';
import { useStore } from '@/utils/vuex';
import {
    BcoStatusV1,
    TestResultTypeOfTestV1,
    priorityV1Options,
    testResultLaboratoryV1Options,
    testResultTypeOfTestV1Options,
    testResultV1Options,
} from '@dbco/enum';
import type { CreateManualTestResultFields } from '@dbco/portal-api/case.dto';
import type { CaseLabel, PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import type { CovidCaseUnion, TaskUnion } from '@dbco/schema/unions';
import { add, sub } from 'date-fns';
import type { FormField } from './formTypes';
import type { Schema } from './schemaType';

const generator = new SchemaGenerator<CovidCaseUnion>();
const taskGenerator = new SchemaGenerator<TaskUnion>();

import * as V1 from './formSchemaV1';
import * as V2 from './formSchemaV2';
import * as V3 from './formSchemaV3';
import * as V4 from './formSchemaV4';
import * as V5 from './formSchemaV5';
import * as V6 from './formSchemaV6';
import * as V7 from './formSchemaV7';
import * as V8 from './formSchemaV8';

type SchemaModule = typeof V1 | typeof V2 | typeof V3 | typeof V4 | typeof V5 | typeof V6 | typeof V7 | typeof V8;
const versions: Record<number, SchemaModule> = {
    1: V1,
    2: V2,
    3: V3,
    4: V4,
    5: V5,
    6: V6,
    7: V7,
    8: V8,
};

export const caseSchema = (
    caseInstance: PlannerCaseListItem | undefined = undefined,
    hasBsn = false,
    caseLabels: CaseLabel[] = [],
    addressVerified = false
) => {
    const elements = [];
    const dobSchema = generator
        .field('index', 'dateOfBirth')
        .dateOfBirth('Geboortedatum (DD-MM-JJJJ, verplicht)', { displayAge: false })
        .appendConfig({ disabled: hasBsn })
        .validation('required', 'Geboortedatum');

    elements.push(
        generator.group([
            generator.field('index', 'initials').text('Initialen').appendConfig({ disabled: hasBsn, class: 'col-3' }),
            generator
                .field('index', 'firstname')
                .text('Voornaam (verplicht)')
                .appendConfig({ disabled: hasBsn, class: 'col' })
                .validation('required', 'Voornaam'),
            generator
                .field('index', 'lastname')
                .text('Achternaam (verplicht)')
                .appendConfig({ disabled: hasBsn, class: 'col' })
                .validation('required', 'Achternaam'),
        ]),
        hasBsn
            ? generator.group([dobSchema], 'mb-4')
            : generator.group([dobSchema, SchemaGenerator.orphanField('index.bsn').text('Volledig BSN')]),
        generator.field('index', 'address').addressLookup('default', false, hasBsn && addressVerified),
        generator.group([
            generator
                .field('contact', 'phone')
                .phone('Mobiel telefoonnummer (verplicht)')
                .validation('required', 'Mobiel telefoonnummer'),
            generator.field('contact', 'email').email('E-mailadres'),
        ]),
        generator.group([
            generator.field('general', 'hpzoneNumber').text('HPZone-nummer (optioneel)').validation(['optional']),
            generator
                .field('test', 'dateOfTest')
                .datePicker('Testdatum')
                .appendConfig({
                    min: formatDate(sub(new Date(), { days: 100 }), 'yyyy-MM-dd'),
                    max: formatDate(new Date(), 'yyyy-MM-dd'),
                    validation: `optional|before:${formatDate(
                        add(new Date(), { days: 1 }),
                        'yyyy-MM-dd'
                    )}|after:${formatDate(sub(new Date(), { days: 101 }), 'yyyy-MM-dd')}`,
                }),
        ]),
        generator.group(
            [
                env.isHpzoneOperational
                    ? generator
                          .field('test', 'monsterNumber')
                          .text('Monsternummer (optioneel)')
                          .validation(['optional', 'monsterNumber'], 'Monsternummer')
                    : generator
                          .field('test', 'monsterNumber')
                          .text('Monsternummer (verplicht)')
                          .validation(['required', 'monsterNumber'], 'Monsternummer'),
            ],
            'mb-4'
        ),
        generator.group(
            [
                SchemaGenerator.orphanField('priority')
                    .dropdown('Prioriteit (optioneel)', 'Geen prioriteit', priorityV1Options)
                    .appendConfig({
                        class: 'col-12 w100',
                    }),
            ],
            'mb-4'
        )
    );

    if (caseInstance?.bcoStatus !== BcoStatusV1.VALUE_archived) {
        elements.push(
            generator.group(
                [
                    SchemaGenerator.orphanField('caseLabels').multiSelectDropdown(
                        'Label (optioneel)',
                        'Geen labels',
                        caseLabels.map((label: CaseLabel) => {
                            return {
                                ...label,
                                value: label.uuid,
                            };
                        }) ?? [],
                        undefined,
                        true,
                        12
                    ),
                ],
                'mb-4'
            )
        );
    }

    if (!caseInstance?.uuid) {
        elements.push(
            generator.group([
                SchemaGenerator.orphanField('notes').textArea('Notitie', '').appendConfig({ maxlength: 5000 }),
            ])
        );
    }

    if (caseInstance?.isDeletable) {
        elements.push(
            generator.group(
                [
                    generator
                        .button('Verwijderen', 'button', 6, 'button-warning', 'inline')
                        .appendConfig({ '@click': 'delete' }),
                ],
                'mb-4'
            )
        );
    }

    return generator.toConfig(elements);
};

/**
 * Schema for creating a place from a suggestion (no editing of suggested data)
 */
export const placeCreateSuggestedSchema = () => {
    return generator.toConfig([
        SchemaGenerator.orphanField('category').placeCategory('Categorie').validation('required', 'Categorie'),
    ]);
};

/**
 * Schema for creating or editing (existing or suggested) a place
 */
export const placeSchema = (showRegionWarning = false) => {
    return generator.toConfig([
        SchemaGenerator.orphanField('label').text('Naam').validation('required'),
        generator.div([SchemaGenerator.orphanField('address').addressLookup('wide', showRegionWarning)], 'container'),
        SchemaGenerator.orphanField('category').placeCategory('Categorie').validation('required', 'Categorie'),
    ]);
};

export const bsnLookupSchemaIndex = () => {
    const meta = useStore().getters['index/meta'];
    return generator.toConfig([
        generator.info(
            generateSafeHtml(
                '<strong>Let op:</strong> gebruik de geboortedatum en het adres waarmee het contact staat ingeschreven in de gemeentelijke basisadministratie.'
            )
        ),
        generator.div(
            [
                meta.pseudoBsnGuid
                    ? generator.group([
                          generator.field('index', 'address').addressLookupSmall().appendConfig({ class: 'col-6' }),
                      ])
                    : generator.group([
                          generator
                              .field('index', 'dateOfBirth')
                              .dateOfBirth('Geboortedatum (DD-MM-JJJJ)', undefined, 3)
                              .validation(['required']),
                          SchemaGenerator.orphanField('index.bsnCensored')
                              .text('Laatste 3 cijfers BSN', undefined, 3)
                              .appendConfig({ maxlength: 3 }),
                          generator.field('index', 'address').addressLookupSmall().appendConfig({ class: 'col-6' }),
                      ]),
            ],
            'container'
        ),
    ]);
};

export const bsnLookupSchemaContact = () => {
    return taskGenerator.toConfig([
        taskGenerator.info(
            generateSafeHtml(
                `<strong>Let op:</strong> gebruik de geboortedatum en het adres waarmee het contact staat ingeschreven in de gemeentelijke basisadministratie.`
            )
        ),
        taskGenerator.div(
            [
                taskGenerator.group([
                    taskGenerator
                        .field('personalDetails', 'dateOfBirth')
                        .dateOfBirth('Geboortedatum (DD-MM-JJJJ)', undefined, 3)
                        .validation(['required']),
                    taskGenerator
                        .field('personalDetails', 'bsnCensored')
                        .text('Laatste 3 cijfers BSN', undefined, 3)
                        .appendConfig({ maxlength: 3 }),
                    taskGenerator
                        .field('personalDetails', 'address')
                        .addressLookupSmall()
                        .appendConfig({ class: 'col-6' }),
                ]),
            ],
            'container'
        ),
    ]);
};

export const complianceSearchByNameSchema = () =>
    generator.toConfig([
        generator.info(
            'Zoek op achternaam + minstens één extra zoekterm. Hoe meer extra zoektermen, hoe completer het zoekresultaat.'
        ),
        generator.div(
            [
                generator.group([
                    SchemaGenerator.orphanField('lastname').text('Achternaam').validation('required').appendConfig({
                        class: 'col-3 w100 mb-3',
                    }),
                    SchemaGenerator.orphanField('email').text('E-mailadres').validation('email').appendConfig({
                        class: 'col-3 w100 mb-3',
                    }),
                    SchemaGenerator.orphanField('dateOfBirth')
                        .dateOfBirth('Geboortedatum (DD-MM-JJJJ)', {
                            displayAge: false,
                        })
                        .appendConfig({
                            class: 'col-3 w100 mb-3',
                        }),
                    SchemaGenerator.orphanField('phone').text('Telefoonnummer').appendConfig({
                        class: 'col-3 w100 mb-3',
                    }),
                ]),
            ],
            'container'
        ),
    ]);

export const complianceSearchByCaseSchema = (values: { identifier?: string } = {}) =>
    generator.toConfig([
        generator.div(
            [
                generator.group([
                    SchemaGenerator.orphanField('identifier')
                        .text('Voer een HPZone-, BCO Portaal- of monsternummer in', '', 10)
                        .validation('ComplianceCaseNr'),
                    generator.button('Case Zoeken', 'submit', 2, 'mt-4', 'block').appendConfig({
                        disabled: Boolean(!values.identifier),
                    }),
                ]),
            ],
            'container'
        ),
    ]);

export const getSchema = (path: string): FormField[] | null => {
    const schemaVersion = store.getters['index/version'] as number | null;

    if (!schemaVersion) return null;
    if (!versions[schemaVersion]) throw 'Schema does not exist';

    const schemaFn: CallableFunction = versions[schemaVersion].getSchema;

    return schemaFn(path);
};

export const getRootSchema = (): Schema | null => {
    const schemaVersion = store.getters['index/version'];

    if (!schemaVersion) return null;
    if (!versions[schemaVersion]) throw 'Schema does not exist';

    return versions[schemaVersion].default;
};

export const manualTestResultCreateFormSchema = () => {
    const generator = new SchemaGenerator<CreateManualTestResultFields>();
    return generator.toConfig([
        generator.field('typeOfTest').dropdown('Type', '', testResultTypeOfTestV1Options),
        generator.slot(
            [generator.field('customTypeOfTest').text('Type (anders)')],
            [{ field: 'typeOfTest', values: [TestResultTypeOfTestV1.VALUE_custom] }]
        ),
        generator.field('dateOfTest').datePicker('Testdatum'),
        generator.field('monsterNumber').text('Monsternummer (optioneel)', '123A012345678912'),
        generator.field('laboratory').inputWithList({
            list: Object.values(testResultLaboratoryV1Options),
            placeholder: 'Laboratorium',
            label: 'Laboratorium',
        }),
        generator.field('result').radioButton('Testuitslag', testResultV1Options),
    ]);
};
