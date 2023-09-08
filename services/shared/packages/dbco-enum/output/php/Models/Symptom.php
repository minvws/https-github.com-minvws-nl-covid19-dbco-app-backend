<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * COVID symptoms
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Symptom.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Symptom nasalCold() nasalCold() Neusverkoudheid
 * @method static Symptom hoarseVoice() hoarseVoice() Schorre stem
 * @method static Symptom soreThroat() soreThroat() Keelpijn
 * @method static Symptom cough() cough() (licht) hoesten
 * @method static Symptom shortnessOfBreath() shortnessOfBreath() Kortademigheid/benauwdheid
 * @method static Symptom painfulBreathing() painfulBreathing() Pijn bij de ademhaling
 * @method static Symptom fever() fever() Koorts (= boven 38 graden Celsius)
 * @method static Symptom coldShivers() coldShivers() Koude rillingen
 * @method static Symptom lossOfSmell() lossOfSmell() Verlies van of verminderde reuk
 * @method static Symptom lossOfTaste() lossOfTaste() Verlies van of verminderde smaak
 * @method static Symptom malaise() malaise() Algehele malaise
 * @method static Symptom fatigue() fatigue() Vermoeidheid
 * @method static Symptom headache() headache() Hoofdpijn
 * @method static Symptom muscleStrain() muscleStrain() Spierpijn
 * @method static Symptom painBehindTheEyes() painBehindTheEyes() Pijn achter de ogen
 * @method static Symptom pain() pain() Algehele pijnklachten
 * @method static Symptom dizziness() dizziness() Duizeligheid
 * @method static Symptom irritableConfused() irritableConfused() Prikkelbaar/verwardheid
 * @method static Symptom lossOfAppetite() lossOfAppetite() Verlies van eetlust
 * @method static Symptom nausea() nausea() Misselijkheid
 * @method static Symptom vomiting() vomiting() Overgeven
 * @method static Symptom diarrhea() diarrhea() Diarree
 * @method static Symptom stomachAche() stomachAche() Buikpijn
 * @method static Symptom pinkEye() pinkEye() Rode prikkende ogen (oogontsteking)
 * @method static Symptom skinCondition() skinCondition() Huidafwijkingen

 * @property-read string $value
*/
final class Symptom extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Symptom',
           'tsConst' => 'symptom',
           'description' => 'COVID symptoms',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'nasal-cold',
               'label' => 'Neusverkoudheid',
               'name' => 'nasalCold',
            ),
            1 =>
            (object) array(
               'value' => 'hoarse-voice',
               'label' => 'Schorre stem',
               'name' => 'hoarseVoice',
            ),
            2 =>
            (object) array(
               'value' => 'sore-throat',
               'label' => 'Keelpijn',
               'name' => 'soreThroat',
            ),
            3 =>
            (object) array(
               'value' => 'cough',
               'label' => '(licht) hoesten',
               'name' => 'cough',
            ),
            4 =>
            (object) array(
               'value' => 'shortness-of-breath',
               'label' => 'Kortademigheid/benauwdheid',
               'name' => 'shortnessOfBreath',
            ),
            5 =>
            (object) array(
               'value' => 'painful-breathing',
               'label' => 'Pijn bij de ademhaling',
               'name' => 'painfulBreathing',
            ),
            6 =>
            (object) array(
               'value' => 'fever',
               'label' => 'Koorts (= boven 38 graden Celsius)',
               'name' => 'fever',
            ),
            7 =>
            (object) array(
               'value' => 'cold-shivers',
               'label' => 'Koude rillingen',
               'name' => 'coldShivers',
            ),
            8 =>
            (object) array(
               'value' => 'loss-of-smell',
               'label' => 'Verlies van of verminderde reuk',
               'name' => 'lossOfSmell',
            ),
            9 =>
            (object) array(
               'value' => 'loss-of-taste',
               'label' => 'Verlies van of verminderde smaak',
               'name' => 'lossOfTaste',
            ),
            10 =>
            (object) array(
               'value' => 'malaise',
               'label' => 'Algehele malaise',
               'name' => 'malaise',
            ),
            11 =>
            (object) array(
               'value' => 'fatigue',
               'label' => 'Vermoeidheid',
               'name' => 'fatigue',
            ),
            12 =>
            (object) array(
               'value' => 'headache',
               'label' => 'Hoofdpijn',
               'name' => 'headache',
            ),
            13 =>
            (object) array(
               'value' => 'muscle-strain',
               'label' => 'Spierpijn',
               'name' => 'muscleStrain',
            ),
            14 =>
            (object) array(
               'value' => 'pain-behind-the-eyes',
               'label' => 'Pijn achter de ogen',
               'name' => 'painBehindTheEyes',
            ),
            15 =>
            (object) array(
               'value' => 'pain',
               'label' => 'Algehele pijnklachten',
               'name' => 'pain',
            ),
            16 =>
            (object) array(
               'value' => 'dizziness',
               'label' => 'Duizeligheid',
               'name' => 'dizziness',
            ),
            17 =>
            (object) array(
               'value' => 'irritable-confused',
               'label' => 'Prikkelbaar/verwardheid',
               'name' => 'irritableConfused',
            ),
            18 =>
            (object) array(
               'value' => 'loss-of-appetite',
               'label' => 'Verlies van eetlust',
               'name' => 'lossOfAppetite',
            ),
            19 =>
            (object) array(
               'value' => 'nausea',
               'label' => 'Misselijkheid',
               'name' => 'nausea',
            ),
            20 =>
            (object) array(
               'value' => 'vomiting',
               'label' => 'Overgeven',
               'name' => 'vomiting',
            ),
            21 =>
            (object) array(
               'value' => 'diarrhea',
               'label' => 'Diarree',
               'name' => 'diarrhea',
            ),
            22 =>
            (object) array(
               'value' => 'stomach-ache',
               'label' => 'Buikpijn',
               'name' => 'stomachAche',
            ),
            23 =>
            (object) array(
               'value' => 'pink-eye',
               'label' => 'Rode prikkende ogen (oogontsteking)',
               'name' => 'pinkEye',
            ),
            24 =>
            (object) array(
               'value' => 'skin-condition',
               'label' => 'Huidafwijkingen',
               'name' => 'skinCondition',
            ),
          ),
        );
    }
}
