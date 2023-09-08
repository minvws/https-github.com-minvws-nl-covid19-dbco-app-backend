<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Type of test result
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResultTypeOfTest.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TestResultTypeOfTest pcr() pcr() Moleculaire diagnostiek (PCR, LAMP, andere nucleinezuuramplificatietest (NAAT))
 * @method static TestResultTypeOfTest antigen() antigen() Antigeen (door zorgprofessional)
 * @method static TestResultTypeOfTest selftest() selftest() Antigeen (zelftest)
 * @method static TestResultTypeOfTest custom() custom() Anders
 * @method static TestResultTypeOfTest unknown() unknown() Onbekend

 * @property-read string $value
 * @property-read TestResultType $type Indicates of Test is lab or selftest
*/
final class TestResultTypeOfTest extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TestResultTypeOfTest',
           'tsConst' => 'testResultTypeOfTest',
           'description' => 'Type of test result',
           'properties' =>
          (object) array(
             'type' =>
            (object) array(
               'type' => 'TestResultType',
               'description' => 'Indicates of Test is lab or selftest',
               'scope' => 'shared',
               'phpType' => 'TestResultType',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'pcr',
               'label' => 'Moleculaire diagnostiek (PCR, LAMP, andere nucleinezuuramplificatietest (NAAT))',
               'type' => 'lab',
               'name' => 'pcr',
            ),
            1 =>
            (object) array(
               'value' => 'antigen',
               'label' => 'Antigeen (door zorgprofessional)',
               'type' => 'lab',
               'name' => 'antigen',
            ),
            2 =>
            (object) array(
               'value' => 'selftest',
               'label' => 'Antigeen (zelftest)',
               'type' => 'selftest',
               'name' => 'selftest',
            ),
            3 =>
            (object) array(
               'value' => 'custom',
               'label' => 'Anders',
               'type' => 'unknown',
               'name' => 'custom',
            ),
            4 =>
            (object) array(
               'value' => 'unknown',
               'label' => 'Onbekend',
               'type' => 'unknown',
               'name' => 'unknown',
            ),
          ),
        );
    }
}
