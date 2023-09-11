<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Intake;

use App\Http\Requests\Api\ApiRequest;

use function is_string;
use function json_decode;

class ListRequest extends ApiRequest
{
    public int $perPage;
    public int $page;
    public ?string $sort = null;
    public ?string $order = null;
    public ?array $filter = [];
    public bool $includeTotal = false;

    protected function passedValidation(): void
    {
        $this->includeTotal = $this->query('includeTotal') === '1';

        if ($this->has('filter') && is_string($this->query('filter'))) {
            $this->filter = json_decode($this->query('filter'), true);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perPage' => 'int|min:0|max:100',
            'page' => 'int|min:1',
            'sort' => 'string|nullable|in:dateOfSymptomOnset,dateOfTest,cat1Count,estimatedCat2Count',
            'order' => 'string|nullable|in:asc,desc',
            'filter' => 'string|nullable|JSON',
            'includeTotal' => 'int|in:0,1',
        ];
    }
}
