<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Reasons of the previous infection suspicion
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PreviousInfectionReason.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PreviousInfectionReason positive() positive() Bewezen met een positieve testuitslag
 * @method static PreviousInfectionReason contact() contact() Gevolg van categorie 1 of 2 contact met iemand die positief getest is

 * @property-read string $value
*/
final class PreviousInfectionReason extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PreviousInfectionReason',
           'tsConst' => 'previousInfectionReason',
           'description' => 'Reasons of the previous infection suspicion',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Bewezen met een positieve testuitslag',
               'value' => 'positive',
               'name' => 'positive',
            ),
            1 =>
            (object) array(
               'label' => 'Gevolg van categorie 1 of 2 contact met iemand die positief getest is',
               'value' => 'contact',
               'name' => 'contact',
            ),
          ),
        );
    }
}
