<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CovidCase;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\EloquentUser;
use App\Models\StatusIndexContactTracing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CasequalityFeedback;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Permission;

use function collect;
use function is_string;

final class UpdateContactStatusRequest extends ApiRequest
{
    public const FIELD_STATUS_INDEX_CONTACT_TRACING = 'status_index_contact_tracing';
    public const FIELD_STATUS_EXPLANATION = 'status_explanation';
    public const FIELD_FORCE_OSIRIS_NOTIFICATION = 'force_osiris_notification';
    public const FIELD_CASEQUALITY_FEEDBACK = 'casequality_feedback';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var EloquentUser $user */
        $user = Auth::user();

        $rules = [
            self::FIELD_STATUS_INDEX_CONTACT_TRACING => [
                'required',
                Rule::in(collect(ContactTracingStatus::all())->map(static fn ($status) => $status->value)),
            ],
            self::FIELD_FORCE_OSIRIS_NOTIFICATION => [
                'string',
                'nullable',
                Rule::in(['finished', 'pre-notification']),
            ],
        ];

        $bcoStatusCompleted = $this->case->bcoStatus === BCOStatus::completed();
        $userCanApprove = $user->can(Permission::caseApprove()->value);

        // if you have permission, you must specify this, otherwise it will reset the approval
        $rules[self::FIELD_CASEQUALITY_FEEDBACK] = $bcoStatusCompleted && $userCanApprove
            ? ['required', 'string']
            : ['prohibited'];

        $explanationRules = ['string', 'max:5000'];
        $explanationRules[] = $this->statusIndexContactTracing()->requiresExplanation() ? 'required' : 'nullable';
        $rules[self::FIELD_STATUS_EXPLANATION] = $explanationRules;

        return $rules;
    }

    public function statusIndexContactTracing(): StatusIndexContactTracing
    {
        return StatusIndexContactTracing::fromString($this->input(self::FIELD_STATUS_INDEX_CONTACT_TRACING, ''));
    }

    public function contactTracingStatus(): ?ContactTracingStatus
    {
        $value = $this->input(self::FIELD_STATUS_INDEX_CONTACT_TRACING, '');

        if (!is_string($value)) {
            throw new InvalidArgumentException('invalid value for status_index_contact_tracing');
        }

        return ContactTracingStatus::tryFrom($value);
    }

    /**
     * Important Note: If a value is given through the request and equal to 'null' we need to return an empty field
     * due to the ConvertEmptyStringsToNull middleware. This is the reason we first check if the request has the
     * specific request key.
     */
    public function statusExplanation(): ?string
    {
        if (!$this->has(self::FIELD_STATUS_EXPLANATION)) {
            return null;
        }

        return $this->input(self::FIELD_STATUS_EXPLANATION) ?? '';
    }

    public function forceOsirisNotification(): ?string
    {
        return $this->input(self::FIELD_FORCE_OSIRIS_NOTIFICATION);
    }

    public function casequalityFeedback(): ?CasequalityFeedback
    {
        $value = $this->input(self::FIELD_CASEQUALITY_FEEDBACK);

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('unexpected value for caseqaulity_feedback');
        }

        return CasequalityFeedback::tryFromOptional($value);
    }
}
