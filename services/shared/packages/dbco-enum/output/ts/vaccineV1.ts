/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Vaccine.json!
 */

/**
 *  values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum VaccineV1 {
  'VALUE_pfizer' = 'pfizer',
  'VALUE_moderna' = 'moderna',
  'VALUE_astrazeneca' = 'astrazeneca',
  'VALUE_janssen' = 'janssen',
  'VALUE_gsk' = 'gsk',
  'VALUE_curevac' = 'curevac',
  'VALUE_novavax' = 'novavax',
  'VALUE_unknown' = 'unknown',
  'VALUE_other' = 'other',
}

/**
 *  options to be used in the forms
 */
export const vaccineV1Options = {
    [VaccineV1.VALUE_pfizer]: "Comirnaty (Pfizer / BioNTech)",
    [VaccineV1.VALUE_moderna]: "Spikevax (Moderna)",
    [VaccineV1.VALUE_astrazeneca]: "Vaxzevria (AstraZeneca)",
    [VaccineV1.VALUE_janssen]: "Janssen Pharmaceutical Companies",
    [VaccineV1.VALUE_gsk]: "Sanofi Pasteur / GSK",
    [VaccineV1.VALUE_curevac]: "CureVac",
    [VaccineV1.VALUE_novavax]: "Nuvaxovid (Novavax)",
    [VaccineV1.VALUE_unknown]: "Onbekend",
    [VaccineV1.VALUE_other]: "Anders"
};
