<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Place;

use App\Http\Requests\Api\ApiRequest;
use MinVWS\DBCO\Enum\Models\ContextCategoryGroup;
use MinVWS\DBCO\Enum\Models\ContextListView;

use function implode;

/**
  * @property-read string $view
  * @property-read string $query
 */
class SearchRequest extends ApiRequest
{
    public int $perPage;
    public int $page;
    public ?string $sort = null;
    public ?string $order = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perPage' => 'int|min:0|max:100',
            'page' => 'int|min:1',
            'view' => 'string|nullable|in:' . implode(',', ContextListView::allValues()),
            'categoryGroup' => 'string|nullable|in:' . implode(',', ContextCategoryGroup::allValues()),
            'isVerified' => 'string|in:true,false',
            'sort' => 'string|nullable|in:verified,updatedAt,createdAt,lastIndexPresence,indexCount,indexCountSinceReset',
            'order' => 'string|nullable|in:asc,desc',
        ];
    }
}
