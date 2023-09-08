<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Vaccine.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Vaccine pfizer() pfizer() Comirnaty (Pfizer / BioNTech)
 * @method static Vaccine moderna() moderna() Spikevax (Moderna)
 * @method static Vaccine astrazeneca() astrazeneca() Vaxzevria (AstraZeneca)
 * @method static Vaccine janssen() janssen() Janssen Pharmaceutical Companies
 * @method static Vaccine gsk() gsk() Sanofi Pasteur / GSK
 * @method static Vaccine curevac() curevac() CureVac
 * @method static Vaccine novavax() novavax() Nuvaxovid (Novavax)
 * @method static Vaccine unknown() unknown() Onbekend
 * @method static Vaccine other() other() Anders

 * @property-read string $value
*/
final class Vaccine extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Vaccine',
           'tsConst' => 'vaccine',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Comirnaty (Pfizer / BioNTech)',
               'value' => 'pfizer',
               'name' => 'pfizer',
            ),
            1 =>
            (object) array(
               'label' => 'Spikevax (Moderna)',
               'value' => 'moderna',
               'name' => 'moderna',
            ),
            2 =>
            (object) array(
               'label' => 'Vaxzevria (AstraZeneca)',
               'value' => 'astrazeneca',
               'name' => 'astrazeneca',
            ),
            3 =>
            (object) array(
               'label' => 'Janssen Pharmaceutical Companies',
               'value' => 'janssen',
               'name' => 'janssen',
            ),
            4 =>
            (object) array(
               'label' => 'Sanofi Pasteur / GSK',
               'value' => 'gsk',
               'name' => 'gsk',
            ),
            5 =>
            (object) array(
               'label' => 'CureVac',
               'value' => 'curevac',
               'name' => 'curevac',
            ),
            6 =>
            (object) array(
               'label' => 'Nuvaxovid (Novavax)',
               'value' => 'novavax',
               'name' => 'novavax',
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
