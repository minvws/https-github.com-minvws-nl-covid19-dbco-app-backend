<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit LabTestIndicator.json!
 *
 * @codeCoverageIgnore
 *
 * @method static LabTestIndicator molecular() molecular() Moleculaire diagnostiek (PCR, LAMP, andere nucleinezuur-amplificatietest (NAAT))
 * @method static LabTestIndicator antigen() antigen() Antigeen(snel)test
 * @method static LabTestIndicator unknown() unknown() Onbekend
 * @method static LabTestIndicator other() other() Anders, namelijk

 * @property-read string $value
*/
final class LabTestIndicator extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'LabTestIndicator',
           'tsConst' => 'labTestIndicator',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Moleculaire diagnostiek (PCR, LAMP, andere nucleinezuur-amplificatietest (NAAT)) ',
               'value' => 'molecular',
               'name' => 'molecular',
            ),
            1 =>
            (object) array(
               'label' => 'Antigeen(snel)test ',
               'value' => 'antigen',
               'name' => 'antigen',
            ),
            2 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
            3 =>
            (object) array(
               'label' => 'Anders, namelijk',
               'value' => 'other',
               'name' => 'other',
            ),
          ),
        );
    }
}
