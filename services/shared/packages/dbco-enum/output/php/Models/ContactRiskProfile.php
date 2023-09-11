<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactRiskProfile.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContactRiskProfile cat1VaccinatedDistancePossible() cat1VaccinatedDistancePossible() Categorie 1 - Wel gevaccineerd - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat1VaccinatedDistanceNotPossible() cat1VaccinatedDistanceNotPossible() Categorie 1 - Wel gevaccineerd - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat1NotVaccinatedDistancePossible() cat1NotVaccinatedDistancePossible() Categorie 1 - Niet gevaccineerd - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat1NotVaccinatedDistanceNotPossible() cat1NotVaccinatedDistanceNotPossible() Categorie 1 - Niet gevaccineerd - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat1VaccinationUnknownDistancePossible() cat1VaccinationUnknownDistancePossible() Categorie 1 - Vaccinatie onbekend - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat1VaccinationUnknownDistanceNotPossible() cat1VaccinationUnknownDistanceNotPossible() Categorie 1 - Vaccinatie onbekend - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat2VaccinatedDistancePossible() cat2VaccinatedDistancePossible() Categorie 2 - Wel gevaccineerd - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat2VaccinatedDistanceNotPossible() cat2VaccinatedDistanceNotPossible() Categorie 2 - Wel gevaccineerd - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat2NotVaccinatedDistancePossible() cat2NotVaccinatedDistancePossible() Categorie 2 - Niet gevaccineerd - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat2NotVaccinatedDistanceNotPossible() cat2NotVaccinatedDistanceNotPossible() Categorie 2 - Niet gevaccineerd - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat2VaccinationUnknownDistancePossible() cat2VaccinationUnknownDistancePossible() Categorie 2 - Vaccinatie onbekend - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat2VaccinationUnknownDistanceNotPossible() cat2VaccinationUnknownDistanceNotPossible() Categorie 2 - Vaccinatie onbekend - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat3VaccinatedDistancePossible() cat3VaccinatedDistancePossible() Categorie 3 - Wel gevaccineerd - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat3VaccinatedDistanceNotPossible() cat3VaccinatedDistanceNotPossible() Categorie 3 - Wel gevaccineerd - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat3NotVaccinatedDistancePossible() cat3NotVaccinatedDistancePossible() Categorie 3 - Niet gevaccineerd - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat3NotVaccinatedDistanceNotPossible() cat3NotVaccinatedDistanceNotPossible() Categorie 3 - Niet gevaccineerd - Afstand houden niet mogelijk
 * @method static ContactRiskProfile cat3VaccinationUnknownDistancePossible() cat3VaccinationUnknownDistancePossible() Categorie 3 - Vaccinatie onbekend - Afstand houden wel mogelijk
 * @method static ContactRiskProfile cat3VaccinationUnknownDistanceNotPossible() cat3VaccinationUnknownDistanceNotPossible() Categorie 3 - Vaccinatie onbekend - Afstand houden niet mogelijk

 * @property-read string $value
*/
final class ContactRiskProfile extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContactRiskProfile',
           'tsConst' => 'contactRiskProfile',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Categorie 1 - Wel gevaccineerd - Afstand houden wel mogelijk',
               'value' => 'cat1_vaccinated_distance_possible',
               'name' => 'cat1VaccinatedDistancePossible',
            ),
            1 =>
            (object) array(
               'label' => 'Categorie 1 - Wel gevaccineerd - Afstand houden niet mogelijk',
               'value' => 'cat1_vaccinated_distance_not_possible',
               'name' => 'cat1VaccinatedDistanceNotPossible',
            ),
            2 =>
            (object) array(
               'label' => 'Categorie 1 - Niet gevaccineerd - Afstand houden wel mogelijk',
               'value' => 'cat1_not_vaccinated_distance_possible',
               'name' => 'cat1NotVaccinatedDistancePossible',
            ),
            3 =>
            (object) array(
               'label' => 'Categorie 1 - Niet gevaccineerd - Afstand houden niet mogelijk',
               'value' => 'cat1_not_vaccinated_distance_not_possible',
               'name' => 'cat1NotVaccinatedDistanceNotPossible',
            ),
            4 =>
            (object) array(
               'label' => 'Categorie 1 - Vaccinatie onbekend - Afstand houden wel mogelijk',
               'value' => 'cat1_vaccination_unknown_distance_possible',
               'name' => 'cat1VaccinationUnknownDistancePossible',
            ),
            5 =>
            (object) array(
               'label' => 'Categorie 1 - Vaccinatie onbekend - Afstand houden niet mogelijk',
               'value' => 'cat1_vaccination_unknown_distance_not_possible',
               'name' => 'cat1VaccinationUnknownDistanceNotPossible',
            ),
            6 =>
            (object) array(
               'label' => 'Categorie 2 - Wel gevaccineerd - Afstand houden wel mogelijk',
               'value' => 'cat2_vaccinated_distance_possible',
               'name' => 'cat2VaccinatedDistancePossible',
            ),
            7 =>
            (object) array(
               'label' => 'Categorie 2 - Wel gevaccineerd - Afstand houden niet mogelijk',
               'value' => 'cat2_vaccinated_distance_not_possible',
               'name' => 'cat2VaccinatedDistanceNotPossible',
            ),
            8 =>
            (object) array(
               'label' => 'Categorie 2 - Niet gevaccineerd - Afstand houden wel mogelijk',
               'value' => 'cat2_not_vaccinated_distance_possible',
               'name' => 'cat2NotVaccinatedDistancePossible',
            ),
            9 =>
            (object) array(
               'label' => 'Categorie 2 - Niet gevaccineerd - Afstand houden niet mogelijk',
               'value' => 'cat2_not_vaccinated_distance_not_possible',
               'name' => 'cat2NotVaccinatedDistanceNotPossible',
            ),
            10 =>
            (object) array(
               'label' => 'Categorie 2 - Vaccinatie onbekend - Afstand houden wel mogelijk',
               'value' => 'cat2_vaccination_unknown_distance_possible',
               'name' => 'cat2VaccinationUnknownDistancePossible',
            ),
            11 =>
            (object) array(
               'label' => 'Categorie 2 - Vaccinatie onbekend - Afstand houden niet mogelijk',
               'value' => 'cat2_vaccination_unknown_distance_not_possible',
               'name' => 'cat2VaccinationUnknownDistanceNotPossible',
            ),
            12 =>
            (object) array(
               'label' => 'Categorie 3 - Wel gevaccineerd - Afstand houden wel mogelijk',
               'value' => 'cat3_vaccinated_distance_possible',
               'name' => 'cat3VaccinatedDistancePossible',
            ),
            13 =>
            (object) array(
               'label' => 'Categorie 3 - Wel gevaccineerd - Afstand houden niet mogelijk',
               'value' => 'cat3_vaccinated_distance_not_possible',
               'name' => 'cat3VaccinatedDistanceNotPossible',
            ),
            14 =>
            (object) array(
               'label' => 'Categorie 3 - Niet gevaccineerd - Afstand houden wel mogelijk',
               'value' => 'cat3_not_vaccinated_distance_possible',
               'name' => 'cat3NotVaccinatedDistancePossible',
            ),
            15 =>
            (object) array(
               'label' => 'Categorie 3 - Niet gevaccineerd - Afstand houden niet mogelijk',
               'value' => 'cat3_not_vaccinated_distance_not_possible',
               'name' => 'cat3NotVaccinatedDistanceNotPossible',
            ),
            16 =>
            (object) array(
               'label' => 'Categorie 3 - Vaccinatie onbekend - Afstand houden wel mogelijk',
               'value' => 'cat3_vaccination_unknown_distance_possible',
               'name' => 'cat3VaccinationUnknownDistancePossible',
            ),
            17 =>
            (object) array(
               'label' => 'Categorie 3 - Vaccinatie onbekend - Afstand houden niet mogelijk',
               'value' => 'cat3_vaccination_unknown_distance_not_possible',
               'name' => 'cat3VaccinationUnknownDistanceNotPossible',
            ),
          ),
        );
    }
}
