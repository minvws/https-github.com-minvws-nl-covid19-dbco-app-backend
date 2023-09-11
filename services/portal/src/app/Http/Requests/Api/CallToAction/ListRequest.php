<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CallToAction;

use App\Http\Requests\Api\ApiRequest;

use function implode;

class ListRequest extends ApiRequest
{
    public int $perPage;
    public int $page;
    public ?string $sort = null;
    public ?string $order = null;

    public function rules(): array
    {
        $allowedSort = [];

        return [
            'perPage' => 'int|min:0|max:100',
            'page' => 'int|min:1',
            'sort' => 'string|nullable|in:' . implode(',', $allowedSort),
            'order' => 'string|nullable|in:asc,desc',
        ];
    }
}
