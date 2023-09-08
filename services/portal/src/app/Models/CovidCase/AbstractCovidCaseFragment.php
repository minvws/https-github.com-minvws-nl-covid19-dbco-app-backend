<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentCase;
use App\Schema\FragmentModel;
use App\Schema\Traits\Compat;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\Codable\Decodable;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

/**
 * @property int $id
 * @property string $case_uuid
 * @property string $fragment_name
 * @property ?string $data
 * @property int $schema_version
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property CarbonImmutable $expires_at
 *
 * @property EloquentCase $covidCase
 */
abstract class AbstractCovidCaseFragment extends FragmentModel implements Decodable, Validatable
{
    use Compat;

    protected $table = 'case_fragment';
    protected static string $encryptionReferenceDateAttribute = 'covidCase.createdAt';
    protected $touches = ['covidCase'];
    protected static string $storageTerm = StorageTerm::LONG;

    public function covidCase(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid');
    }
}
