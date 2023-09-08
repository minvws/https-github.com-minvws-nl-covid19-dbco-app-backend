<?php

declare(strict_types=1);

namespace App\Models\Context;

use App\Models\Eloquent\Context;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Context\Moment\MomentCommon;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;

use function app;

class Moment extends Entity implements SchemaProvider, MomentCommon
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Context\\Moment');
        $schema->setDocumentationIdentifier('context.moment');

        $schema->add(DateTimeType::createField('day', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                $contextModel = $context->getValue('context');
                if ($contextModel instanceof Context) {
                    return (new ContextMomentDateRuleSet($contextModel->case))->create();
                }
                return [];
            })
            ->addFatal(static function (ValidationContext $context) {
                $contextModel = $context->getValue('context');
                if ($contextModel instanceof Context) {
                    return (new ContextMomentDateRuleSet($contextModel->case))->create();
                }
                return [];
            });
        $schema->add(StringType::createField('startTime'))
            ->getValidationRules()
            ->addFatal('date_format:H:i')
            ->addFatal(static function (ValidationContext $context) {
                if ($context->getValue('context') && $context->getValue('context')->general) {
                    return ['prohibited_if:moments.*.day,null'];
                }
                return [];
            })
            ->addFatal(static function (ValidationContext $context) {
                if (!$context->getValue('context')->general) {
                    return ['prohibited_if:moments.*.day,null'];
                }
                return [];
            });
        $schema->add(StringType::createField('endTime'))
            ->getValidationRules()
            ->addFatal('date_format:H:i')
            ->addFatal('after:moments.*.startTime')
            ->addFatal(static function (ValidationContext $context) {
                if ($context->getValue('context') && $context->getValue('context')->general) {
                    return ['prohibited_if:moments.*.day,null'];
                }
                return [];
            })
            ->addFatal(static function (ValidationContext $context) {
                if (!$context->getValue('context')->general) {
                    return ['prohibited_if:moments.*.day,null'];
                }
                return [];
            });
        $schema->add(BoolType::createField('source'));
        $schema->add(StringType::createField('formatted'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
