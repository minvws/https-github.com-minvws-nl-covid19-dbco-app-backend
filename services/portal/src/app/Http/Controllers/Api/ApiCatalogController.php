<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Catalog\IndexRequest;
use App\Http\Requests\Api\Catalog\ShowRequest;
use App\Http\Responses\Api\Catalog\ArrayTypeDecorator;
use App\Http\Responses\Api\Catalog\CatalogDecorator;
use App\Http\Responses\Api\Catalog\EnumTypeDecorator;
use App\Http\Responses\Api\Catalog\FieldDecorator;
use App\Http\Responses\Api\Catalog\PurposeSpecificationDecorator;
use App\Http\Responses\Api\Catalog\SchemaTypeDecorator;
use App\Http\Responses\Api\Catalog\TypeDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Schema\Fields\Field;
use App\Schema\Purpose\PurposeSpecification;
use App\Schema\Types\ArrayType;
use App\Schema\Types\EnumVersionType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use App\Services\CatalogService;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpFoundation\Response;

use function abort_if;

class ApiCatalogController
{
    public function index(IndexRequest $request, CatalogService $catalogService): EncodableResponse
    {
        $index = $catalogService->getIndex($request->options);
        $purpose = $request->options->purpose;

        return
            EncodableResponseBuilder::create($index)
            ->withContext(function (EncodingContext $context) use ($purpose): void {
                    $context->setMode(CatalogDecorator::MODE_INDEX);
                    $context->setValue(CatalogDecorator::PURPOSE, $purpose);
                    $this->registerDecorators($context);
            })
                ->build();
    }

    public function show(ShowRequest $request, CatalogService $catalogService): EncodableResponse
    {
        $type = $catalogService->getType($request->class, $request->version);
        abort_if($type === null, Response::HTTP_NOT_FOUND, 'Class not found');

        $diffToType = null;
        if (isset($request->diffToVersion)) {
            $diffToType = $catalogService->getType($request->class, $request->diffToVersion);
        }

        $purpose = $request->purpose;

        return
            EncodableResponseBuilder::create($type)
            ->withContext(function (EncodingContext $context) use ($diffToType, $purpose): void {
                    $context->setMode(CatalogDecorator::MODE_FULL);
                    $context->setValue(CatalogDecorator::PURPOSE, $purpose);
                    $context->setValue(CatalogDecorator::DIFF_TO_TYPE, $diffToType);
                    $this->registerDecorators($context);
            })
                ->build();
    }

    private function registerDecorators(EncodingContext $context): void
    {
        if ($context->getMode() === CatalogDecorator::MODE_FULL) {
            $context->registerDecorator(Field::class, new FieldDecorator());
            $context->registerDecorator(ArrayType::class, new ArrayTypeDecorator());
            $context->registerDecorator(Type::class, new TypeDecorator());
            $context->registerDecorator(PurposeSpecification::class, new PurposeSpecificationDecorator());
        }

        $context->registerDecorator(EnumVersionType::class, new EnumTypeDecorator());
        $context->registerDecorator(SchemaType::class, new SchemaTypeDecorator());
    }
}
