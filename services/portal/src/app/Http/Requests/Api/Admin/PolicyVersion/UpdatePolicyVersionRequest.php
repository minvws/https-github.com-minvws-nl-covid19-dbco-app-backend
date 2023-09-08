<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\PolicyVersion;

use App\Dto\Admin\UpdatePolicyVersionDto;
use App\Http\Requests\Api\ApiRequest;
use App\Rules\Policy\UniqueStartDate as PolicyVersionUniqueStartDate;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use Webmozart\Assert\Assert;

class UpdatePolicyVersionRequest extends ApiRequest
{
    private UpdatePolicyVersionDto $dto;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, (ValidationRule|array|string)>
     */
    public function rules(): array
    {
        return [
            'name' => ['filled', 'string', 'min:2', 'max:255'],
            'status' => ['filled', 'string', Rule::in(PolicyVersionStatus::allValues())],
            'startDate' => ['filled', 'date', 'after_or_equal:today', new PolicyVersionUniqueStartDate()],
        ];
    }

    public function getDto(): UpdatePolicyVersionDto
    {
        if (isset($this->dto)) {
            return $this->dto;
        }

        return $this->dto = new UpdatePolicyVersionDto(
            name: $this->safeStringOption('name'),
            status: $this->safePolicyVersionStatusOption('status'),
            startDate: $this->safeDateTimeOption('startDate'),
        );
    }

    /**
     * @return Option<string>
     */
    private function safeStringOption(string $key): Option
    {
        if ($this->safe()->missing($key)) {
            return None::create();
        }

        /** @var string $data */
        $data = $this->safe()[$key];

        Assert::string($data);

        return Some::create($data);
    }

    /**
     * @return Option<PolicyVersionStatus>
     */
    private function safePolicyVersionStatusOption(string $key): Option
    {
        if ($this->safe()->missing($key)) {
            return None::create();
        }

        /** @var string $data */
        $data = $this->safe()[$key];

        Assert::string($data);

        return Some::create(PolicyVersionStatus::from($data));
    }

    /**
     * @return Option<CarbonImmutable>
     */
    private function safeDateTimeOption(string $key): Option
    {
        if ($this->safe()->missing($key)) {
            return None::create();
        }

        /** @var string $data */
        $data = $this->safe()[$key];

        Assert::string($data);

        return Some::create(CarbonImmutable::parse($data));
    }
}
