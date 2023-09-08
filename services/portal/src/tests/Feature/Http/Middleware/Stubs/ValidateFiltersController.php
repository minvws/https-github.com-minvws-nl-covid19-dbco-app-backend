<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware\Stubs;

use App\Http\Controllers\Api\Admin\Attributes\RequestHasFixedValuesQueryFilter;
use App\Http\Controllers\Api\Admin\Attributes\RequestQueryFilter;
use App\Http\Controllers\Controller;

final class ValidateFiltersController extends Controller
{
    #[RequestQueryFilter('my_filter_one')]
    protected ?MyBackedEnum $myFilterOne = null;

    #[RequestQueryFilter('my_filter_two')]
    protected ?MyEnum $myFilterTwo = null;

    #[RequestQueryFilter('my_filter_three')]
    protected ?MyBackedEnum $myFilterThree = null;

    #[RequestQueryFilter('my_filter_four')]
    protected ?MyEnum $myFilterFour = null;

    #[RequestHasFixedValuesQueryFilter('my_filter_one', MyBackedEnum::class, required: true)]
    #[RequestHasFixedValuesQueryFilter('my_filter_two', MyEnum::class, required: true)]
    #[RequestHasFixedValuesQueryFilter('my_filter_three', MyBackedEnum::class)]
    #[RequestHasFixedValuesQueryFilter('my_filter_four', MyEnum::class)]
    public function index(): array
    {
        return [
            'hello' => 'world',
            'my_filter_one' => $this->myFilterOne,
            'my_filter_two' => $this->myFilterTwo,
            'my_filter_three' => $this->myFilterThree,
            'my_filter_four' => $this->myFilterFour,
        ];
    }
}
