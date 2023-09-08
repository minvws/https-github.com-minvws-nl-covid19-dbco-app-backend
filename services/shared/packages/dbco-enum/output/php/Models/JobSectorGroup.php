<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Job sector groups.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit JobSectorGroup.json!
 *
 * @codeCoverageIgnore
 *
 * @method static JobSectorGroup care() care() Zorg
 * @method static JobSectorGroup eduDaycare() eduDaycare() Onderwijs / kinderopvang
 * @method static JobSectorGroup foodOrAnimals() foodOrAnimals() Met voedsel of dieren
 * @method static JobSectorGroup other() other() Overig

 * @property-read string $value
 * @property-read \MinVWS\DBCO\Enum\Models\JobSector[] $categories Job sectors that are part of this group.
*/
final class JobSectorGroup extends Enum
{
    use \MinVWS\DBCO\Enum\Traits\JobSectorGroupJobSectors;

    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'JobSectorGroup',
           'tsConst' => 'jobSectorGroup',
           'description' => 'Job sector groups.',
           'traits' =>
          array (
            0 => '\\MinVWS\\DBCO\\Enum\\Traits\\JobSectorGroupJobSectors',
          ),
           'traitProperties' =>
          (object) array(
             'categories' =>
            (object) array(
               'type' => '\\MinVWS\\DBCO\\Enum\\Models\\JobSector[]',
               'description' => 'Job sectors that are part of this group.',
               'method' => 'getJobSectors',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'care',
               'label' => 'Zorg',
               'name' => 'care',
            ),
            1 =>
            (object) array(
               'value' => 'eduDaycare',
               'label' => 'Onderwijs / kinderopvang',
               'name' => 'eduDaycare',
            ),
            2 =>
            (object) array(
               'value' => 'foodOrAnimals',
               'label' => 'Met voedsel of dieren',
               'name' => 'foodOrAnimals',
            ),
            3 =>
            (object) array(
               'value' => 'other',
               'label' => 'Overig',
               'name' => 'other',
            ),
          ),
        );
    }
}
