/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextCategoryGroup.json!
 */

/**
 * Context category groups. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContextCategoryGroupV1 {
  'VALUE_horeca' = 'horeca',
  'VALUE_opvang' = 'opvang',
  'VALUE_onderwijs' = 'onderwijs',
  'VALUE_transport' = 'transport',
  'VALUE_thuis' = 'thuis',
  'VALUE_vereniging' = 'vereniging',
  'VALUE_vvt' = 'vvt',
  'VALUE_zorg' = 'zorg',
  'VALUE_overig' = 'overig',
  'VALUE_anders' = 'anders',
  'VALUE_onbekend' = 'onbekend',
}

/**
 * Context category groups. options to be used in the forms
 */
export const contextCategoryGroupV1Options = [
    {
        "label": "Horeca, Retail & Entertainment",
        "value": ContextCategoryGroupV1.VALUE_horeca,
        "view": "overig"
    },
    {
        "label": "Maatschappelijke opvang (MO) & Penitentiaire Instelling (PI)",
        "value": ContextCategoryGroupV1.VALUE_opvang,
        "view": "overig"
    },
    {
        "label": "Onderwijs / KDV",
        "value": ContextCategoryGroupV1.VALUE_onderwijs,
        "view": "onderwijs"
    },
    {
        "label": "Reizen / vervoer",
        "value": ContextCategoryGroupV1.VALUE_transport,
        "view": "overig"
    },
    {
        "label": "Privésfeer",
        "value": ContextCategoryGroupV1.VALUE_thuis,
        "view": "overig"
    },
    {
        "label": "Vereniging / sport / religieuze bijeenkomst",
        "value": ContextCategoryGroupV1.VALUE_vereniging,
        "view": "overig"
    },
    {
        "label": "Langdurige zorg / VVT",
        "value": ContextCategoryGroupV1.VALUE_vvt,
        "view": "zorg"
    },
    {
        "label": "Zorg / (Para)medische praktijk",
        "value": ContextCategoryGroupV1.VALUE_zorg,
        "view": "zorg"
    },
    {
        "label": "Overig",
        "value": ContextCategoryGroupV1.VALUE_overig,
        "view": "overig"
    },
    {
        "label": "Andere werkplek",
        "value": ContextCategoryGroupV1.VALUE_anders,
        "view": "overig"
    },
    {
        "label": "Onbekend",
        "value": ContextCategoryGroupV1.VALUE_onbekend,
        "view": "overig"
    }
];
