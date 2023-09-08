<?php

declare(strict_types=1);

namespace App\Models\Export;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Purpose\Purpose;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $export_client_id
 * @property Purpose $purpose
 */
class ExportClientPurpose extends Model
{
    use CamelCaseAttributes;

    protected $table = 'export_client_purpose';

    // @phpstan-ignore-next-line
    public $primaryKey = ['export_client_id', 'purpose'];
    public $timestamps = false;
    public $incrementing = false;
    protected $casts = [
        'purpose' => Purpose::class,
    ];

    /**
     * @inheritdoc
     */
    protected function setKeysForSaveQuery($query): Builder
    {
        $query
            ->where('export_client_id', '=', $this->getAttribute('export_client_id'))
            ->where('purpose', '=', $this->getAttribute('purpose'));

        return $query;
    }
}
