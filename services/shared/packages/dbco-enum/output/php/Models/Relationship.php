<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Relationship.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Relationship parent() parent() Ouder
 * @method static Relationship child() child() Kind
 * @method static Relationship sibling() sibling() Broer of zus
 * @method static Relationship partner() partner() Partner
 * @method static Relationship family() family() Familielid (overig)
 * @method static Relationship roommate() roommate() Huisgenoot
 * @method static Relationship friend() friend() Vriend of kennis
 * @method static Relationship student() student() Medestudent of leerling
 * @method static Relationship colleague() colleague() Collega
 * @method static Relationship client() client() Klant
 * @method static Relationship patient() patient() Patiënt
 * @method static Relationship health() health() Gezondheidszorg medewerker
 * @method static Relationship ex() ex() Ex-partner
 * @method static Relationship other() other() Overig

 * @property-read string $value
*/
final class Relationship extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Relationship',
           'tsConst' => 'relationship',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Ouder',
               'value' => 'parent',
               'name' => 'parent',
            ),
            1 =>
            (object) array(
               'label' => 'Kind',
               'value' => 'child',
               'name' => 'child',
            ),
            2 =>
            (object) array(
               'label' => 'Broer of zus',
               'value' => 'sibling',
               'name' => 'sibling',
            ),
            3 =>
            (object) array(
               'label' => 'Partner',
               'value' => 'partner',
               'name' => 'partner',
            ),
            4 =>
            (object) array(
               'label' => 'Familielid (overig)',
               'value' => 'family',
               'name' => 'family',
            ),
            5 =>
            (object) array(
               'label' => 'Huisgenoot',
               'value' => 'roommate',
               'name' => 'roommate',
            ),
            6 =>
            (object) array(
               'label' => 'Vriend of kennis',
               'value' => 'friend',
               'name' => 'friend',
            ),
            7 =>
            (object) array(
               'label' => 'Medestudent of leerling',
               'value' => 'student',
               'name' => 'student',
            ),
            8 =>
            (object) array(
               'label' => 'Collega',
               'value' => 'colleague',
               'name' => 'colleague',
            ),
            9 =>
            (object) array(
               'label' => 'Klant',
               'value' => 'client',
               'name' => 'client',
            ),
            10 =>
            (object) array(
               'label' => 'Patiënt',
               'value' => 'patient',
               'name' => 'patient',
            ),
            11 =>
            (object) array(
               'label' => 'Gezondheidszorg medewerker',
               'value' => 'health',
               'name' => 'health',
            ),
            12 =>
            (object) array(
               'label' => 'Ex-partner',
               'value' => 'ex',
               'name' => 'ex',
            ),
            13 =>
            (object) array(
               'label' => 'Overig',
               'value' => 'other',
               'name' => 'other',
            ),
          ),
        );
    }
}
