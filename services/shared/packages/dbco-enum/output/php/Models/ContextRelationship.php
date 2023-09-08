<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Context relationship
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextRelationship.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContextRelationship staff() staff() Medewerker
 * @method static ContextRelationship visitor() visitor() Bezoeker
 * @method static ContextRelationship resident() resident() Bewoner
 * @method static ContextRelationship patient() patient() Patiënt
 * @method static ContextRelationship teacher() teacher() Docent
 * @method static ContextRelationship student() student() Student / Leerling
 * @method static ContextRelationship traveler() traveler() Reiziger/passagier
 * @method static ContextRelationship unknown() unknown() Onbekend
 * @method static ContextRelationship other() other() Anders

 * @property-read string $value
*/
final class ContextRelationship extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContextRelationship',
           'tsConst' => 'contextRelationship',
           'description' => 'Context relationship',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Medewerker',
               'value' => 'staff',
               'name' => 'staff',
            ),
            1 =>
            (object) array(
               'label' => 'Bezoeker',
               'value' => 'visitor',
               'name' => 'visitor',
            ),
            2 =>
            (object) array(
               'label' => 'Bewoner',
               'value' => 'resident',
               'name' => 'resident',
            ),
            3 =>
            (object) array(
               'label' => 'Patiënt',
               'value' => 'patient',
               'name' => 'patient',
            ),
            4 =>
            (object) array(
               'label' => 'Docent',
               'value' => 'teacher',
               'name' => 'teacher',
            ),
            5 =>
            (object) array(
               'label' => 'Student / Leerling',
               'value' => 'student',
               'name' => 'student',
            ),
            6 =>
            (object) array(
               'label' => 'Reiziger/passagier',
               'value' => 'traveler',
               'name' => 'traveler',
            ),
            7 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
            8 =>
            (object) array(
               'label' => 'Anders',
               'value' => 'other',
               'name' => 'other',
            ),
          ),
        );
    }
}
