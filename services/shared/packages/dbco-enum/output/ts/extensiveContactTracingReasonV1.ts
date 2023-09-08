/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ExtensiveContactTracingReason.json!
 */

/**
 * Extensive Contact Tracing reasons values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ExtensiveContactTracingReasonV1 {
  'VALUE_risk_voc_voi' = 'risk-voc-voi',
  'VALUE_hard_to_reach_group' = 'hard-to-reach-group',
  'VALUE_contacted_people_low_vaccination_coverage' = 'contacted-people-low-vaccination-coverage',
  'VALUE_part_known_cluster' = 'part-known-cluster',
  'VALUE_cant_do_contact_research_various_reasons' = 'cant-do-contact-research-various-reasons',
}

/**
 * Extensive Contact Tracing reasons options to be used in the forms
 */
export const extensiveContactTracingReasonV1Options = [
    {
        "label": "Risico gelopen op een VOC of VOI (bijv. door een reis)",
        "value": ExtensiveContactTracingReasonV1.VALUE_risk_voc_voi,
        "description": "De index heeft (risico gelopen op) een aangetoonde Variant of Interest (VOI) of Variant of Concern (VOC) met beperkte verspreiding in Nederland."
    },
    {
        "label": "Behoort mogelijk tot een moeilijk bereikbare groep",
        "value": ExtensiveContactTracingReasonV1.VALUE_hard_to_reach_group,
        "description": "Denk hierbij aan taalbarrière, culturele verschillen, migratieachtergrond, dak- en thuislozen, ongedocumenteerde personen."
    },
    {
        "label": "Contact gehad met personen met een verwachte lage vaccinatiegraad",
        "value": ExtensiveContactTracingReasonV1.VALUE_contacted_people_low_vaccination_coverage,
        "description": "Vraag aan index of meer dan 50% van de personen die hij/zij gezien heeft in de besmettelijke periode ongevaccineerd zijn. Zo ja, doe uitgebreid BCO. Zo nee, doe standaard BCO. Vaccinatiestatus index speelt hierin geen rol."
    },
    {
        "label": "Mogelijk onderdeel van een (bekend) cluster of situation",
        "value": ExtensiveContactTracingReasonV1.VALUE_part_known_cluster,
        "description": "Vraag aan index of er in diens omgeving (buiten de thuissituatie) besmettingen zijn. Doe uitgebreid BCO als sprake lijkt van een cluster."
    },
    {
        "label": "Kan zelf geen contactonderzoek doen vanwege diverse redenen",
        "value": ExtensiveContactTracingReasonV1.VALUE_cant_do_contact_research_various_reasons,
        "description": "Denk hierbij aan taalbarrière, blind of slechthorend, medische beperking, niet digitaal vaardig."
    }
];
