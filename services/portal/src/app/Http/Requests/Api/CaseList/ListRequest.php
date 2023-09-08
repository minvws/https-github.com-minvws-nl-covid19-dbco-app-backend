<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseList;

use App\Http\Requests\Api\ApiRequest;

use function array_map;
use function explode;
use function is_string;

class ListRequest extends ApiRequest
{
    public function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['types']) && is_string($data['types'])) {
            $data['types'] = array_map('trim', explode(',', $data['types']));
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'perPage' => 'int|min:0|max:1000',
            'page' => 'int|min:1',
            'stats' => 'int|in:1,0',
            'types' => 'array',
            'types.*' => 'string|in:list,queue',
        ];
    }
}
