<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Extensive Contact Tracing reasons
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ExtensiveContactTracingReason.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ExtensiveContactTracingReason riskVocVoi() riskVocVoi() Risico gelopen op een VOC of VOI (bijv. door een reis)
 * @method static ExtensiveContactTracingReason hardToReachGroup() hardToReachGroup() Behoort mogelijk tot een moeilijk bereikbare groep
 * @method static ExtensiveContactTracingReason contactedPeopleLowVaccinationCoverage() contactedPeopleLowVaccinationCoverage() Contact gehad met personen met een verwachte lage vaccinatiegraad
 * @method static ExtensiveContactTracingReason partKnownCluster() partKnownCluster() Mogelijk onderdeel van een (bekend) cluster of situation
 * @method static ExtensiveContactTracingReason cantDoContactResearchVariousReasons() cantDoContactResearchVariousReasons() Kan zelf geen contactonderzoek doen vanwege diverse redenen

 * @property-read string $value
 * @property-read string $description Description
*/
final class ExtensiveContactTracingReason extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ExtensiveContactTracingReason',
           'tsConst' => 'extensiveContactTracingReason',
           'description' => 'Extensive Contact Tracing reasons',
           'properties' =>
          (object) array(
             'description' =>
            (object) array(
               'type' => 'string',
               'description' => 'Description',
               'scope' => 'shared',
               'phpType' => 'string',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'risk-voc-voi',
               'label' => 'Risico gelopen op een VOC of VOI (bijv. door een reis)',
               'description' => 'De index heeft (risico gelopen op) een aangetoonde Variant of Interest (VOI) of Variant of Concern (VOC) met beperkte verspreiding in Nederland.',
               'name' => 'riskVocVoi',
            ),
            1 =>
            (object) array(
               'value' => 'hard-to-reach-group',
               'label' => 'Behoort mogelijk tot een moeilijk bereikbare groep',
               'description' => 'Denk hierbij aan taalbarrière, culturele verschillen, migratieachtergrond, dak- en thuislozen, ongedocumenteerde personen.',
               'name' => 'hardToReachGroup',
            ),
            2 =>
            (object) array(
               'value' => 'contacted-people-low-vaccination-coverage',
               'label' => 'Contact gehad met personen met een verwachte lage vaccinatiegraad',
               'description' => 'Vraag aan index of meer dan 50% van de personen die hij/zij gezien heeft in de besmettelijke periode ongevaccineerd zijn. Zo ja, doe uitgebreid BCO. Zo nee, doe standaard BCO. Vaccinatiestatus index speelt hierin geen rol.',
               'name' => 'contactedPeopleLowVaccinationCoverage',
            ),
            3 =>
            (object) array(
               'value' => 'part-known-cluster',
               'label' => 'Mogelijk onderdeel van een (bekend) cluster of situation',
               'description' => 'Vraag aan index of er in diens omgeving (buiten de thuissituatie) besmettingen zijn. Doe uitgebreid BCO als sprake lijkt van een cluster.',
               'name' => 'partKnownCluster',
            ),
            4 =>
            (object) array(
               'value' => 'cant-do-contact-research-various-reasons',
               'label' => 'Kan zelf geen contactonderzoek doen vanwege diverse redenen',
               'description' => 'Denk hierbij aan taalbarrière, blind of slechthorend, medische beperking, niet digitaal vaardig.',
               'name' => 'cantDoContactResearchVariousReasons',
            ),
          ),
        );
    }
}
