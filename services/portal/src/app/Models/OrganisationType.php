<?php

declare(strict_types=1);

namespace App\Models;

use MinVWS\DBCO\Enum\Models\Enum;

/**
 * @method static OrganisationType regionalGGD() regionalGGD() Regional GGD
 * @method static OrganisationType outsourceOrganisation() outsourceOrganisation() Outsource organisation
 * @method static OrganisationType outsourceDepartment() outsourceDepartment() Outsource department
 * @method static OrganisationType demo() demo() Demo organisation
 * @method static OrganisationType unknown() unknown() Unknown organisation
 */
final class OrganisationType extends Enum
{
    protected static function enumSchema(): object
    {
        return (object) [
            'items' => [
                (object) ['value' => 'regionalGGD', 'label' => 'Regionale GGD'],
                (object) ['value' => 'outsourceOrganisation', 'label' => 'Outsource organisatie'],
                (object) ['value' => 'outsourceDepartment', 'label' => 'Outsource organisatie (geregistreerd als afdeling)'],
                (object) ['value' => 'demo', 'label' => 'Demo organisatie'],
                (object) ['value' => 'unknown', 'label' => 'Unknown organisatie'],
            ],
        ];
    }
}
