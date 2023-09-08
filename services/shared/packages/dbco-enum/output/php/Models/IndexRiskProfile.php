<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IndexRiskProfile.json!
 *
 * @codeCoverageIgnore
 *
 * @method static IndexRiskProfile hospitalAdmitted() hospitalAdmitted() Ziekenhuisopname
 * @method static IndexRiskProfile isImmunoCompromised() isImmunoCompromised() Verminderde afweer
 * @method static IndexRiskProfile hasSymptoms() hasSymptoms() Symptomatische index standaard
 * @method static IndexRiskProfile noSymptoms() noSymptoms() Asymptomatische index standaard

 * @property-read string $value
*/
final class IndexRiskProfile extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'IndexRiskProfile',
           'tsConst' => 'indexRiskProfile',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Ziekenhuisopname',
               'value' => 'hospital_admitted',
               'name' => 'hospitalAdmitted',
            ),
            1 =>
            (object) array(
               'label' => 'Verminderde afweer',
               'value' => 'is_immuno_compromised',
               'name' => 'isImmunoCompromised',
            ),
            2 =>
            (object) array(
               'label' => 'Symptomatische index standaard',
               'value' => 'has_symptoms',
               'name' => 'hasSymptoms',
            ),
            3 =>
            (object) array(
               'label' => 'Asymptomatische index standaard',
               'value' => 'no_symptoms',
               'name' => 'noSymptoms',
            ),
          ),
        );
    }
}
