<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseList;

use App\Http\Requests\Api\ApiRequest;

class GetRequest extends ApiRequest
{
    public bool $stats;

    public function rules(): array
    {
        return [
            'stats' => 'int|in:1,0',
        ];
    }

    protected function passedValidation(): void
    {
        $this->stats = $this->query('stats') === '1';
    }
}
