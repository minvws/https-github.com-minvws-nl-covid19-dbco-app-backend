<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Catalog;

use App\Http\Requests\Api\ApiRequest;
use App\Schema\Purpose\Purpose;
use App\Schema\Purpose\PurposeSpecificationConfig;
use App\Schema\Types\EnumVersionType;
use App\Schema\Types\SchemaType;
use App\Services\CatalogService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Route;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

use function array_map;
use function array_merge;
use function assert;
use function is_array;
use function is_string;
use function response;

class ShowRequest extends ApiRequest
{
    public string $class;
    public ?int $version = null;
    public ?int $diffToVersion = null;
    public ?Purpose $purpose = null;

    public function validationData(): array
    {
        $data = parent::validationData();

        $route = $this->route();
        if ($route instanceof Route) {
            $data = array_merge($data, $route->parametersWithoutNulls());
        }

        return $data;
    }

    public function rules(CatalogService $catalogService): array
    {
        $classString = $this->route('class');
        if (!is_string($classString)) {
            return ['class' => 'string'];
        }

        $type = $catalogService->getType($classString);

        if ($type === null) {
            return ['class' => 'string'];
        }

        $maxVersion = 1;
        if ($type instanceof SchemaType) {
            $maxVersion = $type->getSchemaVersion()->getSchema()->getMaxVersion()->getVersion();
        } elseif ($type instanceof EnumVersionType) {
            $classString = $type->getEnumVersion()->getEnumClass();
            $maxVersion = $classString::getMaxVersion()->getVersion();
        }

        $purposeType = PurposeSpecificationConfig::getConfig()->getPurposeType();

        return [
            'class' => ['string', 'required'],
            'version' => ['int', 'required', 'min:1', 'max:' . $maxVersion],
            'diffToVersion' => ['int', 'min:1', 'lt:version'],
            'purpose' => ['string', Rule::in(array_map(static fn(Purpose $p) => $p->getIdentifier(), $purposeType::cases()))],
        ];
    }

    protected function passedValidation(): void
    {
        $validated = $this->validated();
        assert(is_array($validated));

        $this->class = $validated['class'];
        $this->version = isset($validated['version']) ? (int) $validated['version'] : null;
        $this->diffToVersion = isset($validated['diffToVersion']) ? (int) $validated['diffToVersion'] : null;

        if (empty($validated['purpose'])) {
            return;
        }

        $purposeType = PurposeSpecificationConfig::getConfig()->getPurposeType();
        $this->purpose = $purposeType::from($validated['purpose']);
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
