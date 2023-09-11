/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit BCOType.json!
 */

/**
 * BCO type for case values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum BcoTypeV1 {
  'VALUE_extensive' = 'extensive',
  'VALUE_standard' = 'standard',
  'VALUE_other' = 'other',
  'VALUE_unknown' = 'unknown',
}

/**
 * BCO type for case options to be used in the forms
 */
export const bcoTypeV1Options = [
    {
        "label": "Uitgebreid",
        "value": BcoTypeV1.VALUE_extensive,
        "description": "Als de index tot een doelgroep behoort waarvan het RIVM of de GGD van de index heeft gezegd dat er uitgebreid BCO moet worden gedaan, of als VOI/VOC van toepassing is."
    },
    {
        "label": "Standaard",
        "value": BcoTypeV1.VALUE_standard,
        "description": "Alleen het uitslaggesprek."
    },
    {
        "label": "Anders",
        "value": BcoTypeV1.VALUE_other,
        "description": null
    },
    {
        "label": "Onbekend",
        "value": BcoTypeV1.VALUE_unknown,
        "description": null
    }
];
