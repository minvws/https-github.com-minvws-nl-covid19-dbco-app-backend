<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Context list views
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextListView.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContextListView zorg() zorg() Zorg
 * @method static ContextListView onderwijs() onderwijs() Onderwijs
 * @method static ContextListView overig() overig() Overig

 * @property-read string $value
 * @property-read \MinVWS\DBCO\Enum\Models\ContextCategoryGroup[] $categoryGroups Category groups that are part of this list view.
*/
final class ContextListView extends Enum
{
    use \MinVWS\DBCO\Enum\Traits\ContextListViewGroups;

    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContextListView',
           'tsConst' => 'contextListView',
           'description' => 'Context list views',
           'traits' =>
          array (
            0 => '\\MinVWS\\DBCO\\Enum\\Traits\\ContextListViewGroups',
          ),
           'traitProperties' =>
          (object) array(
             'categoryGroups' =>
            (object) array(
               'type' => '\\MinVWS\\DBCO\\Enum\\Models\\ContextCategoryGroup[]',
               'description' => 'Category groups that are part of this list view.',
               'method' => 'getGroups',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'zorg',
               'label' => 'Zorg',
               'name' => 'zorg',
            ),
            1 =>
            (object) array(
               'value' => 'onderwijs',
               'label' => 'Onderwijs',
               'name' => 'onderwijs',
            ),
            2 =>
            (object) array(
               'value' => 'overig',
               'label' => 'Overig',
               'name' => 'overig',
            ),
          ),
        );
    }
}
