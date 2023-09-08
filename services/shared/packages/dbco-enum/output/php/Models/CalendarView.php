<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * All calendar views
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CalendarView.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CalendarView indexSidebar() indexSidebar() Index sidebar
 * @method static CalendarView indexTaskContagiousSidebar() indexTaskContagiousSidebar() Index contact contagious sidebar
 * @method static CalendarView indexTaskSourceSidebar() indexTaskSourceSidebar() Index contact source sidebar
 * @method static CalendarView indexTaskContagiousTable() indexTaskContagiousTable() Index contact contagious table
 * @method static CalendarView indexTaskSourceTable() indexTaskSourceTable() Index contact source table
 * @method static CalendarView indexContextSidebar() indexContextSidebar() Index context sidebar
 * @method static CalendarView indexContextTable() indexContextTable() Index context table

 * @property-read string $value
*/
final class CalendarView extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CalendarView',
           'tsConst' => 'calendarView',
           'description' => 'All calendar views',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Index sidebar',
               'value' => 'index_sidebar',
               'name' => 'indexSidebar',
            ),
            1 =>
            (object) array(
               'label' => 'Index contact contagious sidebar',
               'value' => 'index_task_contagious_sidebar',
               'name' => 'indexTaskContagiousSidebar',
            ),
            2 =>
            (object) array(
               'label' => 'Index contact source sidebar',
               'value' => 'index_task_source_sidebar',
               'name' => 'indexTaskSourceSidebar',
            ),
            3 =>
            (object) array(
               'label' => 'Index contact contagious table',
               'value' => 'index_task_contagious_table',
               'name' => 'indexTaskContagiousTable',
            ),
            4 =>
            (object) array(
               'label' => 'Index contact source table',
               'value' => 'index_task_source_table',
               'name' => 'indexTaskSourceTable',
            ),
            5 =>
            (object) array(
               'label' => 'Index context sidebar',
               'value' => 'index_context_sidebar',
               'name' => 'indexContextSidebar',
            ),
            6 =>
            (object) array(
               'label' => 'Index context table',
               'value' => 'index_context_table',
               'name' => 'indexContextTable',
            ),
          ),
        );
    }
}
