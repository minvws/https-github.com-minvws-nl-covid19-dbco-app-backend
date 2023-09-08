<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IsolationAdvice.json!
 *
 * @codeCoverageIgnore
 *
 * @method static IsolationAdvice liveSeperatedExplained() liveSeperatedExplained() Gescheiden leven van huisgenoten & schoonmaken van gedeelde keuken en/of badkamer uitgelegd
 * @method static IsolationAdvice isolationImpossibleExplained() isolationImpossibleExplained() Quarantainebeleid wanneer strikte thuisisolatie niet mogelijk is uitgelegd (de laatste dag van quarantaine is afhankelijk van de dag dat index uit isolatie gaat)
 * @method static IsolationAdvice testAdviceHousematesExplained() testAdviceHousematesExplained() Testadvies voor nauwe contacten & huisgenoten uitgelegd

 * @property-read string $value
*/
final class IsolationAdvice extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'IsolationAdvice',
           'tsConst' => 'isolationAdvice',
           'currentVersion' => 2,
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Gescheiden leven van huisgenoten & schoonmaken van gedeelde keuken en/of badkamer uitgelegd',
               'value' => 'live-seperated-explained',
               'name' => 'liveSeperatedExplained',
            ),
            1 =>
            (object) array(
               'label' => 'Quarantainebeleid wanneer strikte thuisisolatie niet mogelijk is uitgelegd (de laatste dag van quarantaine is afhankelijk van de dag dat index uit isolatie gaat)',
               'value' => 'isolation-impossible-explained',
               'maxVersion' => 1,
               'name' => 'isolationImpossibleExplained',
            ),
            2 =>
            (object) array(
               'label' => 'Testadvies voor nauwe contacten & huisgenoten uitgelegd',
               'value' => 'test-advice-housemates-explained',
               'name' => 'testAdviceHousematesExplained',
            ),
          ),
        );
    }
}
