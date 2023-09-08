<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use Symfony\Component\HttpFoundation\Request;

use function app;
use function in_array;
use function request;

/**
 * @property string $phone
 * @property string $email
 */
class Contact extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(StringType::createField('phone'))
            ->getValidationRules()
            ->addFatal(static function () {
                if (
                    !in_array(
                        request()->method(),
                        [Request::METHOD_GET, Request::METHOD_PUT, Request::METHOD_PATCH],
                        true,
                    )
                ) {
                    return ['required', 'string'];
                }
                return [];
            })
            ->addWarning(static function () {
                if (
                    in_array(
                        request()->method(),
                        [Request::METHOD_GET, Request::METHOD_PUT, Request::METHOD_PATCH],
                        true,
                    )
                ) {
                    return ['phone:INTERNATIONAL,NL', 'max:25'];
                }
                return [];
            });
        $schema->add(StringType::createField('email'))
            ->getValidationRules()
            ->addWarning('max:250');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
