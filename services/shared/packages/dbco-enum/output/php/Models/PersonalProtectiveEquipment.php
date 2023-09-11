<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PersonalProtectiveEquipment.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PersonalProtectiveEquipment gloves() gloves() Handschoenen
 * @method static PersonalProtectiveEquipment nonMedicalMask() nonMedicalMask() Niet-medisch mondneuskapje
 * @method static PersonalProtectiveEquipment mask() mask() Medisch mondneuskapje (ten minste type IIR)
 * @method static PersonalProtectiveEquipment glasses() glasses() Bril
 * @method static PersonalProtectiveEquipment handHygiene() handHygiene() Handhygiëne (handen met zeep / alcohol grondig gereinigd)
 * @method static PersonalProtectiveEquipment apron() apron() Schort

 * @property-read string $value
*/
final class PersonalProtectiveEquipment extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PersonalProtectiveEquipment',
           'tsConst' => 'personalProtectiveEquipment',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Handschoenen',
               'value' => 'gloves',
               'name' => 'gloves',
            ),
            1 =>
            (object) array(
               'label' => 'Niet-medisch mondneuskapje',
               'value' => 'non-medical-mask',
               'name' => 'nonMedicalMask',
            ),
            2 =>
            (object) array(
               'label' => 'Medisch mondneuskapje (ten minste type IIR)',
               'value' => 'mask',
               'name' => 'mask',
            ),
            3 =>
            (object) array(
               'label' => 'Bril',
               'value' => 'glasses',
               'name' => 'glasses',
            ),
            4 =>
            (object) array(
               'label' => 'Handhygiëne (handen met zeep / alcohol grondig gereinigd)',
               'value' => 'hand-hygiene',
               'name' => 'handHygiene',
            ),
            5 =>
            (object) array(
               'label' => 'Schort',
               'value' => 'apron',
               'name' => 'apron',
            ),
          ),
        );
    }
}
