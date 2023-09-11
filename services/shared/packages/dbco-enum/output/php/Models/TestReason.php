<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestReason.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TestReason symptoms() symptoms() Klachten
 * @method static TestReason contactWarnedByGgd() contactWarnedByGgd() Gewaarschuwd door GGD na contact met besmet persoon
 * @method static TestReason contact() contact() Gewaarschuwd door een besmet persoon vanwege contact
 * @method static TestReason outbreak() outbreak() Betrokkenheid bij uitbraak (bijv. school / werk / instelling / club)
 * @method static TestReason coronamelder() coronamelder() Melding van CoronaMelder
 * @method static TestReason return() return() Na terugkeer uit risicogebied (vanaf niveau oranje)
 * @method static TestReason work() work() Voor werk
 * @method static TestReason educationDaycare() educationDaycare() Voor onderwijs / kinderopvang
 * @method static TestReason medicalTreatment() medicalTreatment() Voor medische behandeling
 * @method static TestReason event() event() Voor/na evenement (bijv. Fieldlab of testen voor toegang)
 * @method static TestReason meetingPeople() meetingPeople() Na ontmoeting met mensen
 * @method static TestReason meetingManyPeople() meetingManyPeople() Na ontmoeting met (veel) mensen
 * @method static TestReason regularSelftest() regularSelftest() Voor de zekerheid af en toe een zelftest
 * @method static TestReason proofOfRecovery() proofOfRecovery() Voor een herstelbewijs
 * @method static TestReason confirmSelftest() confirmSelftest() Wens tot bevestigen zelftest
 * @method static TestReason other() other() Andere reden

 * @property-read string $value
*/
final class TestReason extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TestReason',
           'tsConst' => 'testReason',
           'currentVersion' => 3,
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Klachten',
               'value' => 'symptoms',
               'name' => 'symptoms',
            ),
            1 =>
            (object) array(
               'label' => 'Gewaarschuwd door GGD na contact met besmet persoon',
               'value' => 'contact_warned_by_ggd',
               'name' => 'contactWarnedByGgd',
            ),
            2 =>
            (object) array(
               'label' => 'Gewaarschuwd door een besmet persoon vanwege contact',
               'value' => 'contact',
               'name' => 'contact',
            ),
            3 =>
            (object) array(
               'label' => 'Betrokkenheid bij uitbraak (bijv. school / werk / instelling / club)',
               'value' => 'outbreak',
               'name' => 'outbreak',
            ),
            4 =>
            (object) array(
               'label' => 'Melding van CoronaMelder',
               'value' => 'coronamelder',
               'maxVersion' => 1,
               'name' => 'coronamelder',
            ),
            5 =>
            (object) array(
               'label' => 'Na terugkeer uit risicogebied (vanaf niveau oranje)',
               'value' => 'return',
               'name' => 'return',
            ),
            6 =>
            (object) array(
               'label' => 'Voor werk',
               'value' => 'work',
               'name' => 'work',
            ),
            7 =>
            (object) array(
               'label' => 'Voor onderwijs / kinderopvang',
               'value' => 'education_daycare',
               'name' => 'educationDaycare',
            ),
            8 =>
            (object) array(
               'label' => 'Voor medische behandeling',
               'value' => 'medical_treatment',
               'name' => 'medicalTreatment',
            ),
            9 =>
            (object) array(
               'label' => 'Voor/na evenement (bijv. Fieldlab of testen voor toegang)',
               'value' => 'event',
               'maxVersion' => 2,
               'name' => 'event',
            ),
            10 =>
            (object) array(
               'label' => 'Na ontmoeting met mensen',
               'value' => 'meeting_people',
               'maxVersion' => 2,
               'name' => 'meetingPeople',
            ),
            11 =>
            (object) array(
               'label' => 'Na ontmoeting met (veel) mensen',
               'value' => 'meeting_many_people',
               'minVersion' => 3,
               'name' => 'meetingManyPeople',
            ),
            12 =>
            (object) array(
               'label' => 'Voor de zekerheid af en toe een zelftest',
               'value' => 'regular_selftest',
               'maxVersion' => 2,
               'name' => 'regularSelftest',
            ),
            13 =>
            (object) array(
               'label' => 'Voor een herstelbewijs',
               'value' => 'proof_of_recovery',
               'minVersion' => 3,
               'name' => 'proofOfRecovery',
            ),
            14 =>
            (object) array(
               'label' => 'Wens tot bevestigen zelftest',
               'value' => 'confirm_selftest',
               'minVersion' => 3,
               'name' => 'confirmSelftest',
            ),
            15 =>
            (object) array(
               'label' => 'Andere reden',
               'value' => 'other',
               'name' => 'other',
            ),
          ),
        );
    }
}
