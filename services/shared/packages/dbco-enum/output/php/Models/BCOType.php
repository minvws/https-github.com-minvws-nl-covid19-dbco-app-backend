<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * BCO type for case
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit BCOType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static BCOType extensive() extensive() Uitgebreid
 * @method static BCOType standard() standard() Standaard
 * @method static BCOType other() other() Anders
 * @method static BCOType unknown() unknown() Onbekend

 * @property-read string $value
 * @property-read string $description Description
*/
final class BCOType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'BCOType',
           'tsConst' => 'bcoType',
           'description' => 'BCO type for case',
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
               'label' => 'Uitgebreid',
               'value' => 'extensive',
               'description' => 'Als de index tot een doelgroep behoort waarvan het RIVM of de GGD van de index heeft gezegd dat er uitgebreid BCO moet worden gedaan, of als VOI/VOC van toepassing is.',
               'name' => 'extensive',
            ),
            1 =>
            (object) array(
               'label' => 'Standaard',
               'value' => 'standard',
               'description' => 'Alleen het uitslaggesprek.',
               'name' => 'standard',
            ),
            2 =>
            (object) array(
               'label' => 'Anders',
               'value' => 'other',
               'name' => 'other',
            ),
            3 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
          ),
        );
    }
}
