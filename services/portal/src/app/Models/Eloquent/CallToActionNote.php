<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

/**
 * @property string $uuid
 * @property string $user_uuid
 * @property string $call_to_action_uuid
 * @property string $note
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property CallToAction $callToAction
 * @property EloquentUser $user
 */
class CallToActionNote extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'call_to_action_note';

    protected $casts = [
        'note' => Sealed::class . ':' . StorageTerm::LONG . ',created_at',
    ];

    protected $fillable = [
        'created_at',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_uuid');
    }

    public function callToAction(): BelongsTo
    {
        return $this->belongsTo(CallToAction::class, 'call_to_action_uuid');
    }
}
