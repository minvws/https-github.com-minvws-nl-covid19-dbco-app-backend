<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Eloquent\EloquentTask;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\General\GeneralCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use Closure;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\TaskGroup;

use function app;

class General extends FragmentCompat implements GeneralCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\General');
        $schema->setDocumentationIdentifier('task.general');

        $schema->add(StringType::createField('reference'))
            ->getValidationRules()
            ->addWarning('min:7')
            ->addWarning('max:16');
        $schema->add(StringType::createField('firstname'))
            ->getValidationRules()
            ->addFatal('max:250');
        $schema->add(StringType::createField('lastname'))
            ->getValidationRules()
            ->addFatal('max:500');
        $schema->add(StringType::createField('email'))
            ->getValidationRules()
            ->addFatal('max:250')
            ->addWarning('email');
        $schema->add(StringType::createField('phone'))
            ->getValidationRules()
            ->addFatal('max:25')
            ->addWarning('phone:INTERNATIONAL,NL');
        $schema->add(DateTimeType::createField('deletedAt'))
            ->setProxyForOwnerField()
            ->setReadOnly();
        $schema->add(DateTimeType::createField('dateOfLastExposure', 'Y-m-d'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addFatal(static fn (ValidationContext $context) => self::fatalRuleForDateOfLastExposure($context))
            ->addWarning(static fn (ValidationContext $context) => self::warningRuleForDateOfLastExposure($context));
        $schema->add(ContactCategory::getVersion(1)->createField('category'))
            ->setProxyForOwnerField();
        $schema->add(BoolType::createField('isSource'))
            ->setProxyForOwnerField()
            ->setDefaultValue(false);
        $schema->add(StringType::createField('label'))
            ->setExternal()
            ->setReadOnly()
            ->getValidationRules()
            ->addFatal('max:100');
        $schema->add(StringType::createField('context'))
            ->setProxyForOwnerField('taskContext')
            ->getValidationRules()
            ->addFatal('max:5000');
        $schema->add(Relationship::getVersion(1)->createField('relationship'));
        $schema->add(StringType::createField('otherRelationship'))
            ->getValidationRules()
            ->addFatal('max:500');
        $schema->add(BoolType::createField('closeContactDuringQuarantine'));
        $schema->add(StringType::createField('nature'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addFatal('max:5000');
        $schema->add(StringType::createField('remarks'))
            ->getValidationRules()
            ->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function getLabelFieldValue(): ?string
    {
        if (!empty($this->firstname) && !empty($this->lastname)) {
            return $this->firstname . ' ' . $this->lastname;
        }

        if (!empty($this->firstname)) {
            return $this->firstname;
        }

        if (!empty($this->getOwnerProxy()->label)) {
            return $this->getOwnerProxy()->label;
        }

        if (!empty($this->getOwnerProxy()->derivedLabel)) {
            return $this->getOwnerProxy()->derivedLabel;
        }

        return null;
    }

    protected function getReferenceFieldValue(Closure $getter): ?string
    {
        // use the owner value if any, else fallback to our own value
        return $this->getOwnerProxy()->dossierNumber ?? $getter();
    }

    protected function setReferenceFieldValue(?string $reference, Closure $setter): void
    {
        // we store the reference both in our own field and in the owner
        $this->getOwnerProxy()->dossierNumber = $reference;
        $setter($reference);
    }

    private static function fatalRuleForDateOfLastExposure(ValidationContext $context): ?string
    {
        /** @var EloquentTask|null $task */
        $task = $context->getData()['task'] ?? null;
        if (!isset($task, $task->covidCase)) {
            return 'prohibited';
        }

        return null;
    }

    /**
     * @return string|array<string>
     */
    private static function warningRuleForDateOfLastExposure(ValidationContext $context): string|array
    {
        /** @var EloquentTask|null $task */
        $task = $context->getData()['task'] ?? null;
        if (!isset($task, $task->covidCase)) {
            return 'prohibited';
        }

        $rule = new DateOfLastExposureRule($task->task_group ?? TaskGroup::contact(), $task->covidCase);
        return $rule->create();
    }
}
