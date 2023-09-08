/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit JobSector.json!
 */

/**
 * Job sectors. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum JobSectorV1 {
  'VALUE_21' = '21',
  'VALUE_22' = '22',
  'VALUE_20' = '20',
  'VALUE_16' = '16',
  'VALUE_17' = '17',
  'VALUE_25' = '25',
  'VALUE_27' = '27',
  'VALUE_4' = '4',
  'VALUE_28' = '28',
  'VALUE_15' = '15',
  'VALUE_29' = '29',
  'VALUE_30' = '30',
  'VALUE_31' = '31',
  'VALUE_13' = '13',
}

/**
 * Job sectors. options to be used in the forms
 */
export const jobSectorV1Options = [
    {
        "label": "Ziekenhuis",
        "value": JobSectorV1.VALUE_21,
        "description": null,
        "group": "care"
    },
    {
        "label": "Verpleeghuis of verzorgingshuis",
        "value": JobSectorV1.VALUE_22,
        "description": null,
        "group": "care"
    },
    {
        "label": "Andere zorg, binnen 1,5 meter afstand van mensen",
        "value": JobSectorV1.VALUE_20,
        "description": null,
        "group": "care"
    },
    {
        "label": "Dagopvang",
        "value": JobSectorV1.VALUE_16,
        "description": "0 tot 4 jaar",
        "group": "eduDaycare"
    },
    {
        "label": "Basisschool en buitenschoolse opvang",
        "value": JobSectorV1.VALUE_17,
        "description": "4 tot 12 jaar",
        "group": "eduDaycare"
    },
    {
        "label": "Middelbaar onderwijs of middelbaar beroepsonderwijs",
        "value": JobSectorV1.VALUE_25,
        "description": "12 jaar en ouder",
        "group": "eduDaycare"
    },
    {
        "label": "Hoger beroepsonderwijs of wetenschappelijk onderwijs",
        "value": JobSectorV1.VALUE_27,
        "description": "16 jaar en ouder",
        "group": "eduDaycare"
    },
    {
        "label": "Werk met dieren, vlees of producten van dierlijk materiaal",
        "value": JobSectorV1.VALUE_4,
        "description": null,
        "group": "foodOrAnimals"
    },
    {
        "label": "Werk met eten en drinken of met het verpakken van eten en drinken in een fabriek of op het land",
        "value": JobSectorV1.VALUE_28,
        "description": null,
        "group": "foodOrAnimals"
    },
    {
        "label": "Horeca met klantcontact",
        "value": JobSectorV1.VALUE_15,
        "description": null,
        "group": "other"
    },
    {
        "label": "Mantelzorg",
        "value": JobSectorV1.VALUE_29,
        "description": null,
        "group": "other"
    },
    {
        "label": "Openbaar vervoer met klantcontact",
        "value": JobSectorV1.VALUE_30,
        "description": null,
        "group": "other"
    },
    {
        "label": "Politie, BOA, marechaussee, brandweer, of Dienst Justitiële Inrichtingen",
        "value": JobSectorV1.VALUE_31,
        "description": null,
        "group": "other"
    },
    {
        "label": "Ander beroep",
        "value": JobSectorV1.VALUE_13,
        "description": null,
        "group": "other"
    }
];
