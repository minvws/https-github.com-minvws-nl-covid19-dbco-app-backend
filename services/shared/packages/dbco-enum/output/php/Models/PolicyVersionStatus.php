<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PolicyVersionStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PolicyVersionStatus draft() draft() Concept
 * @method static PolicyVersionStatus activeSoon() activeSoon() Binnenkort actief
 * @method static PolicyVersionStatus active() active() Actief
 * @method static PolicyVersionStatus old() old() Oud

 * @property-read string $value
*/
final class PolicyVersionStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PolicyVersionStatus',
           'tsConst' => 'policyVersionStatus',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Concept',
               'value' => 'draft',
               'name' => 'draft',
            ),
            1 =>
            (object) array(
               'label' => 'Binnenkort actief',
               'value' => 'active-soon',
               'name' => 'activeSoon',
            ),
            2 =>
            (object) array(
               'label' => 'Actief',
               'value' => 'active',
               'name' => 'active',
            ),
            3 =>
            (object) array(
               'label' => 'Oud',
               'value' => 'old',
               'name' => 'old',
            ),
          ),
        );
    }
}
