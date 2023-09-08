<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit DateOperationRelativeDay.json!
 *
 * @codeCoverageIgnore
 *
 * @method static DateOperationRelativeDay min_1() min_1() 1 dag voor
 * @method static DateOperationRelativeDay min_2() min_2() 2 dagen voor
 * @method static DateOperationRelativeDay min_3() min_3() 3 dagen voor
 * @method static DateOperationRelativeDay min_4() min_4() 4 dagen voor
 * @method static DateOperationRelativeDay min_5() min_5() 5 dagen voor
 * @method static DateOperationRelativeDay min_6() min_6() 6 dagen voor
 * @method static DateOperationRelativeDay min_7() min_7() 7 dagen voor
 * @method static DateOperationRelativeDay min_8() min_8() 8 dagen voor
 * @method static DateOperationRelativeDay min_9() min_9() 9 dagen voor
 * @method static DateOperationRelativeDay min_10() min_10() 10 dagen voor
 * @method static DateOperationRelativeDay min_11() min_11() 11 dagen voor
 * @method static DateOperationRelativeDay min_12() min_12() 12 dagen voor
 * @method static DateOperationRelativeDay min_13() min_13() 13 dagen voor
 * @method static DateOperationRelativeDay min_14() min_14() 14 dagen voor
 * @method static DateOperationRelativeDay min_15() min_15() 15 dagen voor
 * @method static DateOperationRelativeDay zero() zero() Op de dag van
 * @method static DateOperationRelativeDay add_1() add_1() 1 dag na
 * @method static DateOperationRelativeDay add_2() add_2() 2 dagen na
 * @method static DateOperationRelativeDay add_3() add_3() 3 dagen na
 * @method static DateOperationRelativeDay add_4() add_4() 4 dagen na
 * @method static DateOperationRelativeDay add_5() add_5() 5 dagen na
 * @method static DateOperationRelativeDay add_6() add_6() 6 dagen na
 * @method static DateOperationRelativeDay add_7() add_7() 7 dagen na
 * @method static DateOperationRelativeDay add_8() add_8() 8 dagen na
 * @method static DateOperationRelativeDay add_9() add_9() 9 dagen na
 * @method static DateOperationRelativeDay add_10() add_10() 10 dagen na
 * @method static DateOperationRelativeDay add_11() add_11() 11 dagen na
 * @method static DateOperationRelativeDay add_12() add_12() 12 dagen na
 * @method static DateOperationRelativeDay add_12() add_12() 13 dagen na
 * @method static DateOperationRelativeDay add_14() add_14() 14 dagen na
 * @method static DateOperationRelativeDay add_15() add_15() 15 dagen na

 * @property-read string $value
*/
final class DateOperationRelativeDay extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'DateOperationRelativeDay',
           'tsConst' => 'dateOperationRelativeDay',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => '1 dag voor',
               'value' => -1,
               'name' => 'min_1',
            ),
            1 =>
            (object) array(
               'label' => '2 dagen voor',
               'value' => -2,
               'name' => 'min_2',
            ),
            2 =>
            (object) array(
               'label' => '3 dagen voor',
               'value' => -3,
               'name' => 'min_3',
            ),
            3 =>
            (object) array(
               'label' => '4 dagen voor',
               'value' => -4,
               'name' => 'min_4',
            ),
            4 =>
            (object) array(
               'label' => '5 dagen voor',
               'value' => -5,
               'name' => 'min_5',
            ),
            5 =>
            (object) array(
               'label' => '6 dagen voor',
               'value' => -6,
               'name' => 'min_6',
            ),
            6 =>
            (object) array(
               'label' => '7 dagen voor',
               'value' => -7,
               'name' => 'min_7',
            ),
            7 =>
            (object) array(
               'label' => '8 dagen voor',
               'value' => -8,
               'name' => 'min_8',
            ),
            8 =>
            (object) array(
               'label' => '9 dagen voor',
               'value' => -9,
               'name' => 'min_9',
            ),
            9 =>
            (object) array(
               'label' => '10 dagen voor',
               'value' => -10,
               'name' => 'min_10',
            ),
            10 =>
            (object) array(
               'label' => '11 dagen voor',
               'value' => -11,
               'name' => 'min_11',
            ),
            11 =>
            (object) array(
               'label' => '12 dagen voor',
               'value' => -12,
               'name' => 'min_12',
            ),
            12 =>
            (object) array(
               'label' => '13 dagen voor',
               'value' => -13,
               'name' => 'min_13',
            ),
            13 =>
            (object) array(
               'label' => '14 dagen voor',
               'value' => -14,
               'name' => 'min_14',
            ),
            14 =>
            (object) array(
               'label' => '15 dagen voor',
               'value' => -15,
               'name' => 'min_15',
            ),
            15 =>
            (object) array(
               'label' => 'Op de dag van',
               'value' => 0,
               'name' => 'zero',
            ),
            16 =>
            (object) array(
               'label' => '1 dag na',
               'value' => 1,
               'name' => 'add_1',
            ),
            17 =>
            (object) array(
               'label' => '2 dagen na',
               'value' => 2,
               'name' => 'add_2',
            ),
            18 =>
            (object) array(
               'label' => '3 dagen na',
               'value' => 3,
               'name' => 'add_3',
            ),
            19 =>
            (object) array(
               'label' => '4 dagen na',
               'value' => 4,
               'name' => 'add_4',
            ),
            20 =>
            (object) array(
               'label' => '5 dagen na',
               'value' => 5,
               'name' => 'add_5',
            ),
            21 =>
            (object) array(
               'label' => '6 dagen na',
               'value' => 6,
               'name' => 'add_6',
            ),
            22 =>
            (object) array(
               'label' => '7 dagen na',
               'value' => 7,
               'name' => 'add_7',
            ),
            23 =>
            (object) array(
               'label' => '8 dagen na',
               'value' => 8,
               'name' => 'add_8',
            ),
            24 =>
            (object) array(
               'label' => '9 dagen na',
               'value' => 9,
               'name' => 'add_9',
            ),
            25 =>
            (object) array(
               'label' => '10 dagen na',
               'value' => 10,
               'name' => 'add_10',
            ),
            26 =>
            (object) array(
               'label' => '11 dagen na',
               'value' => 11,
               'name' => 'add_11',
            ),
            27 =>
            (object) array(
               'label' => '12 dagen na',
               'value' => 12,
               'name' => 'add_12',
            ),
            28 =>
            (object) array(
               'label' => '13 dagen na',
               'value' => 13,
               'name' => 'add_12',
            ),
            29 =>
            (object) array(
               'label' => '14 dagen na',
               'value' => 14,
               'name' => 'add_14',
            ),
            30 =>
            (object) array(
               'label' => '15 dagen na',
               'value' => 15,
               'name' => 'add_15',
            ),
          ),
        );
    }
}
