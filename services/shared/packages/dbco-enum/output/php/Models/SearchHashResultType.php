<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit SearchHashResultType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static SearchHashResultType index() index() Index
 * @method static SearchHashResultType contact() contact() Contact

 * @property-read string $value
*/
final class SearchHashResultType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'SearchHashResultType',
           'tsConst' => 'searchHashResultType',
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
