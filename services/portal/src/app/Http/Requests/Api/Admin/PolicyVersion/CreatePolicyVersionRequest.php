<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\PolicyVersion;

use App\Dto\Admin\CreatePolicyVersionDto;
use App\Http\Requests\Api\ApiRequest;
use App\Rules\Policy\UniqueStartDate as PolicyVersionUniqueStartDate;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Webmozart\Assert\Assert;

class CreatePolicyVersionRequest extends ApiRequest
{
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'startDate' => ['required', 'date', 'after_or_equal:today', new PolicyVersionUniqueStartDate()],
        ];
    }

    public function getDto(): CreatePolicyVersionDto
    {
        $data = $this->safe();

        Assert::string($data['name']);
        Assert::string($data['startDate']);

        return new CreatePolicyVersionDto(name: $data['name'], startDate: CarbonImmutable::parse($data['startDate']));
    }
}
