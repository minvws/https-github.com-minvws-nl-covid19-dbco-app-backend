<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ExpertQuestion;

use App\Http\Requests\Api\ApiRequest;
use MinVWS\DBCO\Enum\Models\ExpertQuestionSort;

use function implode;

class ListRequest extends ApiRequest
{
    public const TYPE_MEDICAL_SUPERVISION = 'medical-supervision';
    public const TYPE_CONVERSATION_COACH = 'conversation-coach';

    public int $perPage;
    public int $page;
    public ?string $sort = null;
    public ?string $order = null;
    public string $type;

    public function rules(): array
    {
        $allowedSort = [
            ExpertQuestionSort::createdAt()->value,
            ExpertQuestionSort::status()->value,
        ];

        $allowedTypes = [
            self::TYPE_MEDICAL_SUPERVISION,
            self::TYPE_CONVERSATION_COACH,
        ];

        return [
            'perPage' => 'int|min:0|max:100',
            'page' => 'int|min:1',
            'sort' => 'string|nullable|in:' . implode(',', $allowedSort),
            'order' => 'string|nullable|in:asc,desc',
            'type' => 'string|in:' . implode(',', $allowedTypes),
        ];
    }
}
