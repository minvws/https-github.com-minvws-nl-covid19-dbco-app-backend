<?php

declare(strict_types=1);

namespace App\Models;

use MinVWS\Codable\Codable;
use MinVWS\Codable\CodingKeys;
use MinVWS\DBCO\Enum\Models\BCOPhase;

/**
 * @deprecated use \App\Models\Eloquent\EloquentOrganisation, see DBCO-3004
 */
class Organisation implements Codable
{
    use CodingKeys;

    public string $uuid;
    public ?string $abbreviation = null;
    public string $externalId;
    public ?string $hpZoneCode = null;
    public string $name;
    public ?string $phoneNumber = null;
    public BCOPhase $bcoPhase;

    protected array $casts = [
        'bco_phase' => BCOPhase::class,
    ];

    public function toArray(): array
    {
        $organisation = (array) $this;
        $organisation['bco_status'] = $this->bcoPhase->value;

        return $organisation;
    }
}
