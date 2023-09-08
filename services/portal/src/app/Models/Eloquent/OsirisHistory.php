<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;

use function in_array;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property OsirisHistoryStatus $status
 * @property string $osiris_status
 * @property ?OsirisHistoryValidationResponse $osiris_validation_response
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property EloquentCase $case
 */
class OsirisHistory extends EloquentBaseModel
{
    use HasFactory;

    public const OSIRIS_NOTIFICATION_FAILED_LABEL = "osiris_notification_failed";

    protected $table = 'osiris_history';

    protected $fillable = ['case_uuid', 'status', 'osiris_status', 'osiris_validation_response'];

    protected $casts = [
        'status' => OsirisHistoryStatus::class,
        'osiris_validation_response' => 'object',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid', 'uuid');
    }

    public function caseIsReopened(): bool
    {
        return in_array(
            $this->status,
            [OsirisHistoryStatus::failed(), OsirisHistoryStatus::validation()],
            true,
        ) && $this->osiris_status === SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE;
    }
}
