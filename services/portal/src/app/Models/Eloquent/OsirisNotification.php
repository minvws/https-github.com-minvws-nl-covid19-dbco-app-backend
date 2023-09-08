<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property CarbonInterface $notified_at
 * @property string $bco_status
 * @property string $osiris_status
 * @property int $osiris_questionnaire_version
 *
 * @property EloquentCase $case
 */
class OsirisNotification extends EloquentBaseModel
{
    use CamelCaseAttributes;
    use HasFactory;

    protected $table = 'osiris_notification';

    public $timestamps = false;

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    protected $fillable = ['notified_at'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid', 'uuid');
    }

    public static function forCaseExport(EloquentCase $case, string $status, int $osirisQuestionnaireVersion): self
    {
        $notification = new self();
        $notification->uuid = Str::uuid();
        $notification->notified_at = $case->updatedAt;
        $notification->bco_status = $case->bcoStatus;
        $notification->osiris_status = $status;
        $notification->osiris_questionnaire_version = $osirisQuestionnaireVersion;

        return $notification;
    }
}
