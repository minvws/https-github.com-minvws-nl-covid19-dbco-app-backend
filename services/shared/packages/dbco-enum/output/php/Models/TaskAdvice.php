<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TaskAdvice.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TaskAdvice quarantineNotApplicable() quarantineNotApplicable() Quarantaine niet van toepassing
 * @method static TaskAdvice quarantineExplained() quarantineExplained() Uitleg quarantainebeleid als strikte thuisisolatie niet mogelijk is
 * @method static TaskAdvice liveSeperatedExplained() liveSeperatedExplained() Uitleg gescheiden leven van huisgenoten & schoonmaken van gedeelde keuken en/of badkamer
 * @method static TaskAdvice doTestAsap() doTestAsap() Doe zo snel mogelijk een coronatest
 * @method static TaskAdvice doTestWhenSymptoms() doTestWhenSymptoms() Laat je testen wanneer klachten ontstaan
 * @method static TaskAdvice complaintsSelftest() complaintsSelftest() Bij klachten: doe een zelftest
 * @method static TaskAdvice complaintsTestGgd() complaintsTestGgd() Bij klachten: laat je testen bij de GGD. Je hoort bij een doelgroep voor testen bij de GGD

 * @property-read string $value
*/
final class TaskAdvice extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TaskAdvice',
           'tsConst' => 'taskAdvice',
           'currentVersion' => 2,
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'quarantine-not-applicable',
               'label' => 'Quarantaine niet van toepassing',
               'name' => 'quarantineNotApplicable',
            ),
            1 =>
            (object) array(
               'value' => 'quarantine-explained',
               'label' => 'Uitleg quarantainebeleid als strikte thuisisolatie niet mogelijk is',
               'maxVersion' => 1,
               'name' => 'quarantineExplained',
            ),
            2 =>
            (object) array(
               'value' => 'live-seperated-explained',
               'label' => 'Uitleg gescheiden leven van huisgenoten & schoonmaken van gedeelde keuken en/of badkamer',
               'name' => 'liveSeperatedExplained',
            ),
            3 =>
            (object) array(
               'value' => 'do-test-asap',
               'label' => 'Doe zo snel mogelijk een coronatest',
               'maxVersion' => 1,
               'name' => 'doTestAsap',
            ),
            4 =>
            (object) array(
               'value' => 'do-test-when-symptoms',
               'label' => 'Laat je testen wanneer klachten ontstaan',
               'maxVersion' => 1,
               'name' => 'doTestWhenSymptoms',
            ),
            5 =>
            (object) array(
               'value' => 'complaints-selftest',
               'label' => 'Bij klachten: doe een zelftest',
               'minVersion' => 2,
               'name' => 'complaintsSelftest',
            ),
            6 =>
            (object) array(
               'value' => 'complaints-test-ggd',
               'label' => 'Bij klachten: laat je testen bij de GGD. Je hoort bij een doelgroep voor testen bij de GGD',
               'minVersion' => 2,
               'name' => 'complaintsTestGgd',
            ),
          ),
        );
    }
}
