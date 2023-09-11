<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit SelfTestIndicator.json!
 *
 * @codeCoverageIgnore
 *
 * @method static SelfTestIndicator molecular() molecular() Ja, met moleculaire diagnostiek (PCR, LAMP, andere nucleïnezuur amplificatietest (NAAT))
 * @method static SelfTestIndicator antigen() antigen() Ja, met antigeen(snel)test
 * @method static SelfTestIndicator plannedRetest() plannedRetest() Nee, hertest volgt op een later moment
 * @method static SelfTestIndicator noRetest() noRetest() Nee, er wordt geen hertest uitgevoerd
 * @method static SelfTestIndicator unknown() unknown() Onbekend

 * @property-read string $value
 * @property-read int $osirisCode
*/
final class SelfTestIndicator extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'SelfTestIndicator',
           'tsConst' => 'selfTestIndicator',
           'properties' =>
          (object) array(
             'osirisCode' =>
            (object) array(
               'type' => 'int',
               'scope' => 'php',
               'phpType' => 'int',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Ja, met moleculaire diagnostiek (PCR, LAMP, andere nucleïnezuur amplificatietest (NAAT))',
               'value' => 'molecular',
               'osirisCode' => 1,
               'name' => 'molecular',
            ),
            1 =>
            (object) array(
               'label' => 'Ja, met antigeen(snel)test',
               'value' => 'antigen',
               'osirisCode' => 2,
               'name' => 'antigen',
            ),
            2 =>
            (object) array(
               'label' => 'Nee, hertest volgt op een later moment ',
               'value' => 'planned-retest',
               'osirisCode' => 3,
               'name' => 'plannedRetest',
            ),
            3 =>
            (object) array(
               'label' => 'Nee, er wordt geen hertest uitgevoerd',
               'value' => 'no-retest',
               'osirisCode' => 4,
               'name' => 'noRetest',
            ),
            4 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'osirisCode' => 5,
               'name' => 'unknown',
            ),
          ),
        );
    }
}
