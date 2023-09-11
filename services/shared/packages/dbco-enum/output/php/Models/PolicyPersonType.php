<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PolicyPersonType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PolicyPersonType index() index() Index
 * @method static PolicyPersonType contact() contact() Contact

 * @property-read string $value
*/
final class PolicyPersonType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PolicyPersonType',
           'tsConst' => 'policyPersonType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Index',
               'value' => 'index',
               'name' => 'index',
            ),
            1 =>
            (object) array(
               'label' => 'Contact',
               'value' => 'contact',
               'name' => 'contact',
            ),
          ),
        );
    }
}
