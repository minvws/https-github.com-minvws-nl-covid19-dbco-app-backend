<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * BCO phase
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit BCOPhase.json!
 *
 * @codeCoverageIgnore
 *
 * @method static BCOPhase phaseNone() phaseNone() Geen Fase
 * @method static BCOPhase phase1() phase1() Fase 1
 * @method static BCOPhase phase2() phase2() Fase 2
 * @method static BCOPhase phase3() phase3() Fase 3
 * @method static BCOPhase phase4() phase4() Fase 4
 * @method static BCOPhase phase5() phase5() Fase 5
 * @method static BCOPhase phaseSteekproef() phaseSteekproef() Fase Steekproef

 * @property-read string $value
*/
final class BCOPhase extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'BCOPhase',
           'tsConst' => 'bcoPhase',
           'description' => 'BCO phase',
           'default' => '5',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Geen Fase',
               'value' => 'none',
               'name' => 'phaseNone',
            ),
            1 =>
            (object) array(
               'label' => 'Fase 1',
               'value' => '1',
               'name' => 'phase1',
            ),
            2 =>
            (object) array(
               'label' => 'Fase 2',
               'value' => '2',
               'name' => 'phase2',
            ),
            3 =>
            (object) array(
               'label' => 'Fase 3',
               'value' => '3',
               'name' => 'phase3',
            ),
            4 =>
            (object) array(
               'label' => 'Fase 4',
               'value' => '4',
               'name' => 'phase4',
            ),
            5 =>
            (object) array(
               'label' => 'Fase 5',
               'value' => '5',
               'name' => 'phase5',
            ),
            6 =>
            (object) array(
               'label' => 'Fase Steekproef',
               'value' => 'steekproef',
               'name' => 'phaseSteekproef',
            ),
          ),
        );
    }
}
