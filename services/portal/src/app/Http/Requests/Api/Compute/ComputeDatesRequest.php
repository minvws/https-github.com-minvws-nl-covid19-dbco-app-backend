<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Compute;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function is_null;
use function is_string;
use function sprintf;

class ComputeDatesRequest extends FormRequest
{
    private const DATE_FORMAT = 'Y-m-d';
    private const FIELD_EPISODE_START_DATE = 'episodeStartDate';
    private const FIELD_HAS_SYMPTOMS = 'hasSymptoms';
    private const FIELD_DATE_OF_SYMPTOM_ONSET = 'dateOfSymptomOnset';
    private const FIELD_DATE_OF_TEST = 'dateOfTest';
    private const FIELD_IS_HOSPITAL_ADMITTED = 'isHospitalAdmitted';
    private const FIELD_HOSPITAL_REASON = 'hospitalReason';
    private const FIELD_IS_IMMUNE = 'isImmune';
    private const FIELD_HAS_UNDERLYING_SUFFERING_OR_MEDICATION = 'hasUnderlyingSufferingOrMedication';
    private const FIELD_IS_IMMUNO_COMPROMISED = 'isImmunoCompromised';
    private const FIELD_STILL_HAD_SYMPTOMS_AT = 'stillHadSymptomsAt';

    public function rules(): array
    {
        return [
            self::FIELD_EPISODE_START_DATE => [
                'required',
                sprintf('date_format:%s', self::DATE_FORMAT),
            ],
            self::FIELD_HAS_SYMPTOMS => [
                'required',
                Rule::in([YesNoUnknown::no(), YesNoUnknown::yes()]),
            ],
            self::FIELD_DATE_OF_SYMPTOM_ONSET => [
                'nullable',
                'required_if:hasSymptoms,' . YesNoUnknown::yes(),
                sprintf('date_format:%s', self::DATE_FORMAT),
                'before_or_equal:today',
            ],
            self::FIELD_DATE_OF_TEST => [
                'required_if:hasSymptoms,' . YesNoUnknown::no(),
                sprintf('date_format:%s', self::DATE_FORMAT),
                'before_or_equal:today',
            ],
            self::FIELD_IS_HOSPITAL_ADMITTED => [
                'nullable',
                Rule::in([YesNoUnknown::no(), YesNoUnknown::yes()]),
            ],
            self::FIELD_HOSPITAL_REASON => [
                'nullable',
                Rule::in(HospitalReason::allValues()),
            ],
            self::FIELD_IS_IMMUNE => [
                'nullable',
                Rule::in([YesNoUnknown::no(), YesNoUnknown::yes()]),
            ],
            self::FIELD_HAS_UNDERLYING_SUFFERING_OR_MEDICATION => [
                'nullable',
                Rule::in(YesNoUnknown::allValues()),
            ],
            self::FIELD_IS_IMMUNO_COMPROMISED => [
                'nullable',
                Rule::in(YesNoUnknown::allValues()),
            ],
            self::FIELD_STILL_HAD_SYMPTOMS_AT => [
                'nullable',
                sprintf('date_format:%s', self::DATE_FORMAT),
                'before_or_equal:today',
            ],
        ];
    }

    public function getDateOfEpisodeStart(): CarbonInterface
    {
        $date = $this->formatDate($this->getOptionalStringFromPost(self::FIELD_EPISODE_START_DATE));
        assert($date instanceof CarbonInterface);

        return $date;
    }

    public function getDateOfTest(): ?CarbonInterface
    {
        return $this->formatDate($this->getOptionalStringFromPost(self::FIELD_DATE_OF_TEST));
    }

    public function getDateOfSymptomsOnset(): ?CarbonInterface
    {
        return $this->formatDate($this->getOptionalStringFromPost(self::FIELD_DATE_OF_SYMPTOM_ONSET));
    }

    public function getHasSymptoms(): ?YesNoUnknown
    {
        return YesNoUnknown::fromOptional($this->getOptionalStringFromPost(self::FIELD_HAS_SYMPTOMS));
    }

    public function getIsImmune(): ?YesNoUnknown
    {
        return YesNoUnknown::fromOptional($this->getOptionalStringFromPost(self::FIELD_IS_IMMUNE));
    }

    public function getHasUnderlyingSuffering(): ?YesNoUnknown
    {
        return YesNoUnknown::fromOptional($this->getOptionalStringFromPost(self::FIELD_HAS_UNDERLYING_SUFFERING_OR_MEDICATION));
    }

    public function getIsImmunoComprimised(): ?YesNoUnknown
    {
        return YesNoUnknown::fromOptional($this->getOptionalStringFromPost(self::FIELD_IS_IMMUNO_COMPROMISED));
    }

    public function getIsHospitalAdmitted(): ?YesNoUnknown
    {
        return YesNoUnknown::fromOptional($this->getOptionalStringFromPost(self::FIELD_IS_HOSPITAL_ADMITTED));
    }

    public function getHospitalReason(): ?HospitalReason
    {
        return HospitalReason::fromOptional($this->getOptionalStringFromPost(self::FIELD_HOSPITAL_REASON));
    }

    public function getStillHadSymptomsAt(): ?CarbonInterface
    {
        return $this->formatDate($this->getOptionalStringFromPost(self::FIELD_STILL_HAD_SYMPTOMS_AT));
    }

    private function getOptionalStringFromPost(string $key): ?string
    {
        $value = $this->post($key);
        assert(is_string($value) || is_null($value));

        return $value;
    }

    private function formatDate(?string $value): ?CarbonInterface
    {
        if ($value === null) {
            return null;
        }

        $date = CarbonImmutable::createFromFormat(self::DATE_FORMAT, $value);

        if ($date === false) { //phpstan fix
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $date;
    }
}
