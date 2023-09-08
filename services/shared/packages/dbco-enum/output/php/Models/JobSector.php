<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Job sectors.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit JobSector.json!
 *
 * @codeCoverageIgnore
 *
 * @method static JobSector ziekenhuis() ziekenhuis() Ziekenhuis
 * @method static JobSector verpleeghuisOfVerzorgingshuis() verpleeghuisOfVerzorgingshuis() Verpleeghuis of verzorgingshuis
 * @method static JobSector andereZorg() andereZorg() Andere zorg, binnen 1,5 meter afstand van mensen
 * @method static JobSector dagopvang() dagopvang() Dagopvang
 * @method static JobSector basisschoolEnBuitenschoolseOpvang() basisschoolEnBuitenschoolseOpvang() Basisschool en buitenschoolse opvang
 * @method static JobSector middelbaarOnderwijsOfMiddelbaarBeroepsonderwijs() middelbaarOnderwijsOfMiddelbaarBeroepsonderwijs() Middelbaar onderwijs of middelbaar beroepsonderwijs
 * @method static JobSector medewerkerHogerOnderwijs() medewerkerHogerOnderwijs() Hoger beroepsonderwijs of wetenschappelijk onderwijs
 * @method static JobSector werkMetDierenOfDierlijkeProducten() werkMetDierenOfDierlijkeProducten() Werk met dieren, vlees of producten van dierlijk materiaal
 * @method static JobSector werkMetEtenOfDrinken() werkMetEtenOfDrinken() Werk met eten en drinken of met het verpakken van eten en drinken in een fabriek of op het land
 * @method static JobSector horeca() horeca() Horeca met klantcontact
 * @method static JobSector mantelzorg() mantelzorg() Mantelzorg
 * @method static JobSector openbaarvervoer() openbaarvervoer() Openbaar vervoer met klantcontact
 * @method static JobSector politieBrandweer() politieBrandweer() Politie, BOA, marechaussee, brandweer, of Dienst Justitiële Inrichtingen
 * @method static JobSector andereBeroep() andereBeroep() Ander beroep

 * @property-read string $value
 * @property-read JobSectorGroup $group Job sector group
*/
final class JobSector extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'JobSector',
           'tsConst' => 'jobSector',
           'description' => 'Job sectors.',
           'properties' =>
          (object) array(
             'description' =>
            (object) array(
               'type' => 'string',
               'description' => 'Description',
               'scope' => 'ts',
               'phpType' => 'string',
            ),
             'group' =>
            (object) array(
               'type' => 'JobSectorGroup',
               'description' => 'Job sector group',
               'scope' => 'shared',
               'phpType' => 'JobSectorGroup',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => '21',
               'name' => 'ziekenhuis',
               'label' => 'Ziekenhuis',
               'description' => null,
               'group' => 'care',
            ),
            1 =>
            (object) array(
               'value' => '22',
               'name' => 'verpleeghuisOfVerzorgingshuis',
               'label' => 'Verpleeghuis of verzorgingshuis',
               'description' => null,
               'group' => 'care',
            ),
            2 =>
            (object) array(
               'value' => '20',
               'name' => 'andereZorg',
               'label' => 'Andere zorg, binnen 1,5 meter afstand van mensen',
               'description' => null,
               'group' => 'care',
            ),
            3 =>
            (object) array(
               'value' => '16',
               'name' => 'dagopvang',
               'label' => 'Dagopvang',
               'description' => '0 tot 4 jaar',
               'group' => 'eduDaycare',
            ),
            4 =>
            (object) array(
               'value' => '17',
               'name' => 'basisschoolEnBuitenschoolseOpvang',
               'label' => 'Basisschool en buitenschoolse opvang',
               'description' => '4 tot 12 jaar',
               'group' => 'eduDaycare',
            ),
            5 =>
            (object) array(
               'value' => '25',
               'name' => 'middelbaarOnderwijsOfMiddelbaarBeroepsonderwijs',
               'label' => 'Middelbaar onderwijs of middelbaar beroepsonderwijs',
               'description' => '12 jaar en ouder',
               'group' => 'eduDaycare',
            ),
            6 =>
            (object) array(
               'value' => '27',
               'name' => 'medewerkerHogerOnderwijs',
               'label' => 'Hoger beroepsonderwijs of wetenschappelijk onderwijs',
               'description' => '16 jaar en ouder',
               'group' => 'eduDaycare',
            ),
            7 =>
            (object) array(
               'value' => '4',
               'name' => 'werkMetDierenOfDierlijkeProducten',
               'label' => 'Werk met dieren, vlees of producten van dierlijk materiaal',
               'description' => null,
               'group' => 'foodOrAnimals',
            ),
            8 =>
            (object) array(
               'value' => '28',
               'name' => 'werkMetEtenOfDrinken',
               'label' => 'Werk met eten en drinken of met het verpakken van eten en drinken in een fabriek of op het land',
               'description' => null,
               'group' => 'foodOrAnimals',
            ),
            9 =>
            (object) array(
               'value' => '15',
               'name' => 'horeca',
               'label' => 'Horeca met klantcontact',
               'description' => null,
               'group' => 'other',
            ),
            10 =>
            (object) array(
               'value' => '29',
               'name' => 'mantelzorg',
               'label' => 'Mantelzorg',
               'description' => null,
               'group' => 'other',
            ),
            11 =>
            (object) array(
               'value' => '30',
               'name' => 'openbaarvervoer',
               'label' => 'Openbaar vervoer met klantcontact',
               'description' => null,
               'group' => 'other',
            ),
            12 =>
            (object) array(
               'value' => '31',
               'name' => 'politieBrandweer',
               'label' => 'Politie, BOA, marechaussee, brandweer, of Dienst Justitiële Inrichtingen',
               'description' => null,
               'group' => 'other',
            ),
            13 =>
            (object) array(
               'value' => '13',
               'name' => 'andereBeroep',
               'label' => 'Ander beroep',
               'description' => null,
               'group' => 'other',
            ),
          ),
        );
    }
}
