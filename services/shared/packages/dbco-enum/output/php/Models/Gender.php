<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Gender selection
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Gender.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Gender male() male() Man
 * @method static Gender female() female() Vrouw
 * @method static Gender other() other() Overig

 * @property-read string $value
 * @property-read string $mittensCode
*/
final class Gender extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Gender',
           'tsConst' => 'gender',
           'description' => 'Gender selection',
           'properties' =>
          (object) array(
             'mittensCode' =>
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
               'label' => 'Man',
               'value' => 'male',
               'mittensCode' => 'M',
               'name' => 'male',
            ),
            1 =>
            (object) array(
               'label' => 'Vrouw',
               'value' => 'female',
               'mittensCode' => 'V',
               'name' => 'female',
            ),
            2 =>
            (object) array(
               'label' => 'Overig',
               'value' => 'other',
               'mittensCode' => 'O',
               'name' => 'other',
            ),
          ),
        );
    }
}
