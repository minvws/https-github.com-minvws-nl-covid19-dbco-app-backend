<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Other professions.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ProfessionOther.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ProfessionOther kapper() kapper() Kapper
 * @method static ProfessionOther schoonheidsspecialist() schoonheidsspecialist() Schoonheidsspecialist
 * @method static ProfessionOther manicure() manicure() Manicure
 * @method static ProfessionOther pedicure() pedicure() Pedicure
 * @method static ProfessionOther rijinstructeur() rijinstructeur() Rij-instructeur
 * @method static ProfessionOther winkelmedewerker() winkelmedewerker() Winkelmedewerker
 * @method static ProfessionOther trainer() trainer() Trainer/sport instructeur
 * @method static ProfessionOther anders() anders() Anders

 * @property-read string $value
*/
final class ProfessionOther extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ProfessionOther',
           'tsConst' => 'professionOther',
           'description' => 'Other professions.',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'kapper',
               'label' => 'Kapper',
               'name' => 'kapper',
            ),
            1 =>
            (object) array(
               'value' => 'schoonheidsspecialist',
               'label' => 'Schoonheidsspecialist',
               'name' => 'schoonheidsspecialist',
            ),
            2 =>
            (object) array(
               'value' => 'manicure',
               'label' => 'Manicure',
               'name' => 'manicure',
            ),
            3 =>
            (object) array(
               'value' => 'pedicure',
               'label' => 'Pedicure',
               'name' => 'pedicure',
            ),
            4 =>
            (object) array(
               'value' => 'rijinstructeur',
               'label' => 'Rij-instructeur',
               'name' => 'rijinstructeur',
            ),
            5 =>
            (object) array(
               'value' => 'winkelmedewerker',
               'label' => 'Winkelmedewerker',
               'name' => 'winkelmedewerker',
            ),
            6 =>
            (object) array(
               'value' => 'trainer',
               'label' => 'Trainer/sport instructeur',
               'name' => 'trainer',
            ),
            7 =>
            (object) array(
               'value' => 'anders',
               'label' => 'Anders',
               'name' => 'anders',
            ),
          ),
        );
    }
}
