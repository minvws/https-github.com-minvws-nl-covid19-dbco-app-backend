<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CovidMeasure.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CovidMeasure spatscherm() spatscherm() Spatscherm voor gezicht
 * @method static CovidMeasure ventilate() ventilate() Ventileren
 * @method static CovidMeasure plexiglass() plexiglass() Plexiglas afscheidingen
 * @method static CovidMeasure distance() distance() 1,5 meter afstand houden
 * @method static CovidMeasure walkingRoutes() walkingRoutes() Looproutes
 * @method static CovidMeasure personalProtectiveEquipment() personalProtectiveEquipment() Persoonlijke beschermingsmiddelen
 * @method static CovidMeasure nonMedicalMasks() nonMedicalMasks() Niet-medische mondmaskers
 * @method static CovidMeasure handHygiene() handHygiene() Mogelijkheden tot goede handhygiëne
 * @method static CovidMeasure coronaCheckApp() coronaCheckApp() Controle CoronaCheck app

 * @property-read string $value
*/
final class CovidMeasure extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CovidMeasure',
           'tsConst' => 'covidMeasure',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Spatscherm voor gezicht',
               'value' => 'spatscherm',
               'name' => 'spatscherm',
            ),
            1 =>
            (object) array(
               'label' => 'Ventileren',
               'value' => 'ventilate',
               'name' => 'ventilate',
            ),
            2 =>
            (object) array(
               'label' => 'Plexiglas afscheidingen',
               'value' => 'plexiglass',
               'name' => 'plexiglass',
            ),
            3 =>
            (object) array(
               'label' => '1,5 meter afstand houden',
               'value' => 'distance',
               'name' => 'distance',
            ),
            4 =>
            (object) array(
               'label' => 'Looproutes',
               'value' => 'walking-routes',
               'name' => 'walkingRoutes',
            ),
            5 =>
            (object) array(
               'label' => 'Persoonlijke beschermingsmiddelen',
               'value' => 'personal-protective-equipment',
               'name' => 'personalProtectiveEquipment',
            ),
            6 =>
            (object) array(
               'label' => 'Niet-medische mondmaskers',
               'value' => 'non-medical-masks',
               'name' => 'nonMedicalMasks',
            ),
            7 =>
            (object) array(
               'label' => 'Mogelijkheden tot goede handhygiëne',
               'value' => 'hand-hygiene',
               'name' => 'handHygiene',
            ),
            8 =>
            (object) array(
               'label' => 'Controle CoronaCheck app',
               'value' => 'corona-check-app',
               'name' => 'coronaCheckApp',
            ),
          ),
        );
    }
}
