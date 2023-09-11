<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\MessageStatus;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;

/**
 * @property string $uuid
 * @property string $user_uuid
 * @property string $case_uuid
 * @property ?string $task_uuid
 * @property string $mail_template
 * @property MessageTemplateType $message_template_type
 * @property EmailLanguage $mail_language
 * @property string $mailer_identifier
 * @property string $from_name
 * @property string $from_email
 * @property string $to_name
 * @property string $to_email
 * @property ?string $telephone
 * @property string $subject
 * @property string $text
 * @property MessageStatus $status
 * @property ?CarbonInterface $notification_sent_at
 * @property ?CarbonInterface $expires_at
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property ?CarbonInterface $deleted_at
 * @property bool $identity_required
 * @property ?string $pseudo_bsn
 * @property ?CarbonInterface $case_created_at Used as (reference to the) encryption key, make sure you set the value for this property before the fields to seal!
 * @property bool $is_secure
 *
 * @property Collection<int, Attachment> $attachments
 * @property EloquentCase $case
 * @property ?EloquentTask $task
 */
class EloquentMessage extends EloquentBaseModel
{
    use HasFactory;
    use SoftDeletes;
    use CamelCaseAttributes;

    /** @var string $table */
    protected $table = 'message';

    /** @var array<string, string> $casts */
    public $casts = [
        'mail_language' => EmailLanguage::class,
        'identity_required' => 'boolean',
        'is_secure' => 'boolean',
        'message_template_type' => MessageTemplateType::class,
        'mailer_identifier' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'from_name' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'from_email' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'to_name' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'to_email' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'telephone' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'subject' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'text' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
    ];

    protected $fillable = [
        'text',
    ];

    public function getDates(): array
    {
        return [
            'created_at',
            'updated_at',
            'notification_sent_at',
            'expires_at',
            'case_created_at',
            'deleted_at',
        ];
    }

    public function attachments(): BelongsToMany
    {
        return $this
            ->belongsToMany(Attachment::class, 'message_attachment', 'message_uuid', 'attachment_uuid')
            ->orderBy('attachment.file_name');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(EloquentTask::class);
    }
}
