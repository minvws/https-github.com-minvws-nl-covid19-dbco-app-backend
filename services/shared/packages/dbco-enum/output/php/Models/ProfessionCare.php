<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Care professions.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ProfessionCare.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ProfessionCare verzorger() verzorger() Verzorger
 * @method static ProfessionCare verpleegkundige() verpleegkundige() Verpleegkundige
 * @method static ProfessionCare arts() arts() Arts
 * @method static ProfessionCare tandarts() tandarts() Tandarts/mondhygiënist
 * @method static ProfessionCare dietist() dietist() Diëtist
 * @method static ProfessionCare huidtherapeut() huidtherapeut() Huidtherapeut
 * @method static ProfessionCare logopedist() logopedist() Logopedist
 * @method static ProfessionCare fysiotherapeut() fysiotherapeut() Fysiotherapeut/ergotherapeut/oefentherapeut/podotherapeut
 * @method static ProfessionCare orthoptist() orthoptist() Optometrist/orthoptist
 * @method static ProfessionCare audiocien() audiocien() Audioloog/audiocien
 * @method static ProfessionCare thuiszorg() thuiszorg() Thuiszorgmedewerker
 * @method static ProfessionCare anders() anders() Anders

 * @property-read string $value
*/
final class ProfessionCare extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ProfessionCare',
           'tsConst' => 'professionCare',
           'description' => 'Care professions.',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'verzorger',
               'label' => 'Verzorger',
               'name' => 'verzorger',
            ),
            1 =>
            (object) array(
               'value' => 'verpleegkundige',
               'label' => 'Verpleegkundige',
               'name' => 'verpleegkundige',
            ),
            2 =>
            (object) array(
               'value' => 'arts',
               'label' => 'Arts',
               'name' => 'arts',
            ),
            3 =>
            (object) array(
               'value' => 'tandarts',
               'label' => 'Tandarts/mondhygiënist',
               'name' => 'tandarts',
            ),
            4 =>
            (object) array(
               'value' => 'dietist',
               'label' => 'Diëtist',
               'name' => 'dietist',
            ),
            5 =>
            (object) array(
               'value' => 'huidtherapeut',
               'label' => 'Huidtherapeut',
               'name' => 'huidtherapeut',
            ),
            6 =>
            (object) array(
               'value' => 'logopedist',
               'label' => 'Logopedist',
               'name' => 'logopedist',
            ),
            7 =>
            (object) array(
               'value' => 'fysiotherapeut',
               'label' => 'Fysiotherapeut/ergotherapeut/oefentherapeut/podotherapeut ',
               'name' => 'fysiotherapeut',
            ),
            8 =>
            (object) array(
               'value' => 'orthoptist',
               'label' => 'Optometrist/orthoptist',
               'name' => 'orthoptist',
            ),
            9 =>
            (object) array(
               'value' => 'audiocien',
               'label' => 'Audioloog/audiocien',
               'name' => 'audiocien',
            ),
            10 =>
            (object) array(
               'value' => 'thuiszorg',
               'label' => 'Thuiszorgmedewerker',
               'name' => 'thuiszorg',
            ),
            11 =>
            (object) array(
               'value' => 'anders',
               'label' => 'Anders',
               'name' => 'anders',
            ),
          ),
        );
    }
}
