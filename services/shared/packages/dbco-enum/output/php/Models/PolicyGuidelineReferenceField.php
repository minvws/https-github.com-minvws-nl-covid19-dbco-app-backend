<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PolicyGuidelineReferenceField.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PolicyGuidelineReferenceField dateOfSymptomOnset() dateOfSymptomOnset() Eerste ziektedag
 * @method static PolicyGuidelineReferenceField dateOfTest() dateOfTest() Testdatum

 * @property-read string $value
 * @property-read string $property
*/
final class PolicyGuidelineReferenceField extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PolicyGuidelineReferenceField',
           'tsConst' => 'policyGuidelineReferenceField',
           'properties' =>
          (object) array(
             'property' =>
            (object) array(
               'type' => 'string',
               'scope' => 'php',
               'phpType' => 'string',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Eerste ziektedag',
               'value' => 'date_of_symptom_onset',
               'property' => 'dateOfSymptomOnset',
               'name' => 'dateOfSymptomOnset',
            ),
            1 =>
            (object) array(
               'label' => 'Testdatum',
               'value' => 'date_of_test',
               'property' => 'dateOfTest',
               'name' => 'dateOfTest',
            ),
          ),
        );
    }
}
