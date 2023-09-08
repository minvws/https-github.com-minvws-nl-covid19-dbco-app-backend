<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit VaccinationGroup.json!
 *
 * @codeCoverageIgnore
 *
 * @method static VaccinationGroup residentNursingHome() residentNursingHome() Bewoner verpleeghuis
 * @method static VaccinationGroup residentInstitution() residentInstitution() Bewoner instelling voor mensen met een verstandelijke beperking
 * @method static VaccinationGroup healthcareWorker() healthcareWorker() Gezondheidszorgmedewerker
 * @method static VaccinationGroup ageAbove60() ageAbove60() Ouder dan 60 jaar
 * @method static VaccinationGroup ageBelow60MedicalCondition() ageBelow60MedicalCondition() Jonger dan 60 jaar met medische indicatie
 * @method static VaccinationGroup ageBelow60() ageBelow60() Jonger dan 60 jaar zonder medische indicatie

 * @property-read string $value
*/
final class VaccinationGroup extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'VaccinationGroup',
           'tsConst' => 'vaccinationGroup',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Bewoner verpleeghuis',
               'value' => 'resident-nursing-home',
               'name' => 'residentNursingHome',
            ),
            1 =>
            (object) array(
               'label' => 'Bewoner instelling voor mensen met een verstandelijke beperking',
               'value' => 'resident-institution',
               'name' => 'residentInstitution',
            ),
            2 =>
            (object) array(
               'label' => 'Gezondheidszorgmedewerker',
               'value' => 'healthcare-worker',
               'name' => 'healthcareWorker',
            ),
            3 =>
            (object) array(
               'label' => 'Ouder dan 60 jaar',
               'value' => 'age-above-60',
               'name' => 'ageAbove60',
            ),
            4 =>
            (object) array(
               'label' => 'Jonger dan 60 jaar met medische indicatie',
               'value' => 'age-below-60-medical-condition',
               'name' => 'ageBelow60MedicalCondition',
            ),
            5 =>
            (object) array(
               'label' => 'Jonger dan 60 jaar zonder medische indicatie',
               'value' => 'age-below-60',
               'name' => 'ageBelow60',
            ),
          ),
        );
    }
}
