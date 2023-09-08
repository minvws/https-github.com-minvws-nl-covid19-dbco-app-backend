<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Contact categories.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactCategory.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContactCategory cat1() cat1() 1 - Huisgenoot (Leven in dezelfde woonomgeving en langdurig contact op minder dan 1,5 meter)
 * @method static ContactCategory cat2a() cat2a() 2A - Nauw contact (Opgeteld meer dan 15 minuten binnen 1,5 meter)
 * @method static ContactCategory cat2b() cat2b() 2B - Nauw contact (Opgeteld minder dan 15 minuten binnen 1,5 meter, met hoogrisico contact)
 * @method static ContactCategory cat3a() cat3a() 3A - Overig contact (Opgeteld meer dan 15 minuten op meer dan 1,5 meter, in dezelfde ruimte)
 * @method static ContactCategory cat3b() cat3b() 3B - Overig contact (Opgeteld minder dan 15 minuten binnen 1,5 meter, zonder hoogrisico contact, in dezelfde ruimte of buiten)

 * @property-read string $value
*/
final class ContactCategory extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContactCategory',
           'tsConst' => 'contactCategory',
           'description' => 'Contact categories.',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => '1',
               'name' => 'cat1',
               'label' => '1 - Huisgenoot (Leven in dezelfde woonomgeving en langdurig contact op minder dan 1,5 meter)',
            ),
            1 =>
            (object) array(
               'value' => '2a',
               'name' => 'cat2a',
               'label' => '2A - Nauw contact (Opgeteld meer dan 15 minuten binnen 1,5 meter)',
            ),
            2 =>
            (object) array(
               'value' => '2b',
               'name' => 'cat2b',
               'label' => '2B - Nauw contact (Opgeteld minder dan 15 minuten binnen 1,5 meter, met hoogrisico contact)',
            ),
            3 =>
            (object) array(
               'value' => '3a',
               'name' => 'cat3a',
               'label' => '3A - Overig contact (Opgeteld meer dan 15 minuten op meer dan 1,5 meter, in dezelfde ruimte)',
            ),
            4 =>
            (object) array(
               'value' => '3b',
               'name' => 'cat3b',
               'label' => '3B - Overig contact (Opgeteld minder dan 15 minuten binnen 1,5 meter, zonder hoogrisico contact, in dezelfde ruimte of buiten)',
            ),
          ),
        );
    }
}
