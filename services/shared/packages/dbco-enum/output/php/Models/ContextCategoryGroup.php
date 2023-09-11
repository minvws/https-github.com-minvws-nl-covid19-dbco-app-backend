<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Context category groups.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextCategoryGroup.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContextCategoryGroup horeca() horeca() Horeca, Retail & Entertainment
 * @method static ContextCategoryGroup opvang() opvang() Maatschappelijke opvang (MO) & Penitentiaire Instelling (PI)
 * @method static ContextCategoryGroup onderwijs() onderwijs() Onderwijs / KDV
 * @method static ContextCategoryGroup transport() transport() Reizen / vervoer
 * @method static ContextCategoryGroup thuis() thuis() Privésfeer
 * @method static ContextCategoryGroup vereniging() vereniging() Vereniging / sport / religieuze bijeenkomst
 * @method static ContextCategoryGroup vvt() vvt() Langdurige zorg / VVT
 * @method static ContextCategoryGroup zorg() zorg() Zorg / (Para)medische praktijk
 * @method static ContextCategoryGroup overig() overig() Overig
 * @method static ContextCategoryGroup anders() anders() Andere werkplek
 * @method static ContextCategoryGroup onbekend() onbekend() Onbekend

 * @property-read string $value
 * @property-read ContextListView $view Context list view
 * @property-read \MinVWS\DBCO\Enum\Models\ContextCategory[] $categories Categories that are part of this group.
*/
final class ContextCategoryGroup extends Enum
{
    use \MinVWS\DBCO\Enum\Traits\ContextCategoryGroupCategories;

    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContextCategoryGroup',
           'tsConst' => 'contextCategoryGroup',
           'description' => 'Context category groups.',
           'traits' =>
          array (
            0 => '\\MinVWS\\DBCO\\Enum\\Traits\\ContextCategoryGroupCategories',
          ),
           'traitProperties' =>
          (object) array(
             'categories' =>
            (object) array(
               'type' => '\\MinVWS\\DBCO\\Enum\\Models\\ContextCategory[]',
               'description' => 'Categories that are part of this group.',
               'method' => 'getCategories',
            ),
          ),
           'properties' =>
          (object) array(
             'view' =>
            (object) array(
               'type' => 'ContextListView',
               'description' => 'Context list view',
               'scope' => 'shared',
               'phpType' => 'ContextListView',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'horeca',
               'label' => 'Horeca, Retail & Entertainment',
               'view' => 'overig',
               'name' => 'horeca',
            ),
            1 =>
            (object) array(
               'value' => 'opvang',
               'label' => 'Maatschappelijke opvang (MO) & Penitentiaire Instelling (PI)',
               'view' => 'overig',
               'name' => 'opvang',
            ),
            2 =>
            (object) array(
               'value' => 'onderwijs',
               'label' => 'Onderwijs / KDV',
               'view' => 'onderwijs',
               'name' => 'onderwijs',
            ),
            3 =>
            (object) array(
               'value' => 'transport',
               'label' => 'Reizen / vervoer',
               'view' => 'overig',
               'name' => 'transport',
            ),
            4 =>
            (object) array(
               'value' => 'thuis',
               'label' => 'Privésfeer',
               'view' => 'overig',
               'name' => 'thuis',
            ),
            5 =>
            (object) array(
               'value' => 'vereniging',
               'label' => 'Vereniging / sport / religieuze bijeenkomst',
               'view' => 'overig',
               'name' => 'vereniging',
            ),
            6 =>
            (object) array(
               'value' => 'vvt',
               'label' => 'Langdurige zorg / VVT',
               'view' => 'zorg',
               'name' => 'vvt',
            ),
            7 =>
            (object) array(
               'value' => 'zorg',
               'label' => 'Zorg / (Para)medische praktijk',
               'view' => 'zorg',
               'name' => 'zorg',
            ),
            8 =>
            (object) array(
               'value' => 'overig',
               'label' => 'Overig',
               'view' => 'overig',
               'name' => 'overig',
            ),
            9 =>
            (object) array(
               'value' => 'anders',
               'label' => 'Andere werkplek',
               'view' => 'overig',
               'name' => 'anders',
            ),
            10 =>
            (object) array(
               'value' => 'onbekend',
               'label' => 'Onbekend',
               'view' => 'overig',
               'name' => 'onbekend',
            ),
          ),
        );
    }
}
