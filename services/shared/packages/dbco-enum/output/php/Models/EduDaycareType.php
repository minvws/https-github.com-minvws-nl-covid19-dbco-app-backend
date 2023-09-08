<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit EduDaycareType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static EduDaycareType vocationalOrProfessionalEducationOrUniversity() vocationalOrProfessionalEducationOrUniversity() Middelbaar- of Hoger Beroepsonderwijs of een Universiteit
 * @method static EduDaycareType secondaryEducation() secondaryEducation() (speciaal) Voortgezet Onderwijs (VWO, HAVO, VMBO, praktijkschool)
 * @method static EduDaycareType primaryEducation() primaryEducation() (speciaal) Basisonderwijs
 * @method static EduDaycareType unknown() unknown() Onbekend

 * @property-read string $value
*/
final class EduDaycareType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'EduDaycareType',
           'tsConst' => 'eduDaycareType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Middelbaar- of Hoger Beroepsonderwijs of een Universiteit',
               'value' => 'vocational-or-professional-education-or-university',
               'name' => 'vocationalOrProfessionalEducationOrUniversity',
            ),
            1 =>
            (object) array(
               'label' => '(speciaal) Voortgezet Onderwijs (VWO, HAVO, VMBO, praktijkschool)',
               'value' => 'secondary-education',
               'name' => 'secondaryEducation',
            ),
            2 =>
            (object) array(
               'label' => '(speciaal) Basisonderwijs',
               'value' => 'primary-education',
               'name' => 'primaryEducation',
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
