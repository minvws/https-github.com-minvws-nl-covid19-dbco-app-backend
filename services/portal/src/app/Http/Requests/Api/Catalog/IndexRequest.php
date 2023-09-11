<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Catalog;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Catalog\Category;
use App\Models\Catalog\Filter;
use App\Models\Catalog\Options;
use App\Schema\Purpose\Purpose;
use App\Schema\Purpose\PurposeSpecificationConfig;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

use function array_map;
use function assert;
use function explode;
use function is_array;
use function is_string;
use function response;
use function strval;

class IndexRequest extends ApiRequest
{
    public Options $options;

    public function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['categories']) && is_string($data['categories'])) {
            $data['categories'] = array_map('trim', explode(',', $data['categories']));
        }

        return $data;
    }

    protected function passedValidation(): void
    {
        $validated = $this->validated();
        assert(is_array($validated));
        $optionsContainer = new Options();

        if (isset($validated['query'])) {
            $optionsContainer->query = strval($validated['query']);
        }

        $purpose = $validated['purpose'] ?? null;

        if (!empty($purpose)) {
            $purposeType = PurposeSpecificationConfig::getConfig()->getPurposeType();
            $optionsContainer->purpose = $purposeType::from(strval($purpose));
        }

        $categories = $validated['categories'] ?? null;
        if (!empty($categories)) {
            assert(is_array($categories));
            $optionsContainer->categories = array_map(static fn (string $c) => Category::from($c), $categories);
        }

        $optionsContainer->filter = Filter::Main;

        if (isset($validated['filter'])) {
            $optionsContainer->filter = Filter::from($validated['filter']);
        }

        $this->options = $optionsContainer;
    }

    public function rules(): array
    {
        $purposeType = PurposeSpecificationConfig::getConfig()->getPurposeType();

        return [
            'query' => ['string'],
            'purpose' => ['string', Rule::in(array_map(static fn (Purpose $p) => $p->getIdentifier(), $purposeType::cases()))],
            'categories' => ['array'],
            'categories.*' => ['string', Rule::in(array_map(static fn (Category $c) => $c->value, Category::cases()))],
            'filter' => ['string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response(
            [
                'errors' => $validator->errors(),
            ],
            Response::HTTP_BAD_REQUEST,
        ));
    }
}
