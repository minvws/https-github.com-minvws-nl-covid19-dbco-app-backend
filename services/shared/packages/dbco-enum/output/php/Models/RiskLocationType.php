<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit RiskLocationType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static RiskLocationType nursingHome() nursingHome() Verpleeghuis of woonzorgcentrum
 * @method static RiskLocationType disabledResidentalCar() disabledResidentalCar() Instelling voor verstandelijk en/of lichamelijk beperkten
 * @method static RiskLocationType ggzInstitution() ggzInstitution() GGZ-instelling (geestelijke gezondheidszorg)
 * @method static RiskLocationType assistedLiving() assistedLiving() Begeleid kleinschalig wonen
 * @method static RiskLocationType prison() prison() Penitentiaire instelling
 * @method static RiskLocationType youthInstitution() youthInstitution() (Semi-)residentiele jeugdinstelling
 * @method static RiskLocationType asylumCenter() asylumCenter() Asielzoekerscentrum / opvang voor vluchtelingen
 * @method static RiskLocationType socialLiving() socialLiving() Overige maatschappelijke opvang
 * @method static RiskLocationType other() other() Anders

 * @property-read string $value
*/
final class RiskLocationType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'RiskLocationType',
           'tsConst' => 'riskLocationType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Verpleeghuis of woonzorgcentrum',
               'value' => 'nursing-home',
               'name' => 'nursingHome',
            ),
            1 =>
            (object) array(
               'label' => 'Instelling voor verstandelijk en/of lichamelijk beperkten',
               'value' => 'disabled-residental-car',
               'name' => 'disabledResidentalCar',
            ),
            2 =>
            (object) array(
               'label' => 'GGZ-instelling (geestelijke gezondheidszorg)',
               'value' => 'ggz-institution',
               'name' => 'ggzInstitution',
            ),
            3 =>
            (object) array(
               'label' => 'Begeleid kleinschalig wonen',
               'value' => 'assisted-living',
               'name' => 'assistedLiving',
            ),
            4 =>
            (object) array(
               'label' => 'Penitentiaire instelling',
               'value' => 'prison',
               'name' => 'prison',
            ),
            5 =>
            (object) array(
               'label' => '(Semi-)residentiele jeugdinstelling',
               'value' => 'youth-institution',
               'name' => 'youthInstitution',
            ),
            6 =>
            (object) array(
               'label' => 'Asielzoekerscentrum / opvang voor vluchtelingen',
               'value' => 'asylum-center',
               'name' => 'asylumCenter',
            ),
            7 =>
            (object) array(
               'label' => 'Overige maatschappelijke opvang',
               'value' => 'social-living',
               'name' => 'socialLiving',
            ),
            8 =>
            (object) array(
               'label' => 'Anders',
               'value' => 'other',
               'name' => 'other',
            ),
          ),
        );
    }
}
