<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Organisation;
use App\Models\Versions\CovidCase\General\GeneralCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\ObjectType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Validation\Rule;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use Throwable;

use function app;
use function config;

/**
 * @property string $reference
 * @property string $hpzoneNumber
 */
class General extends FragmentCompat implements GeneralCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\General');
        $schema->setDocumentationIdentifier('covidCase.general');

        $schema->add(StringType::createField('source'))
            ->setProxyForOwnerField('source')
            ->getValidationRules()
            ->addFatal('max:250');
        $schema->add(StringType::createField('reference'))
            ->setProxyForOwnerField('case_id')
            ->getValidationRules()
            ->addFatal('required')
            ->addFatal(static function (ValidationContext $context) {
                    $caseUuid = $context->getData()['caseUuid'] ?? null;
                    return
                        Rule::unique('covidcase', 'case_id')
                            ->ignore($caseUuid, 'uuid');
            });
        $schema->add(StringType::createField('hpzoneNumber'))
            ->setProxyForOwnerField('hpzone_number')
            ->getValidationRules()
            ->addFatal('digits_between:6,8')
            ->addFatal(static function (ValidationContext $context) {
                    $caseUuid = $context->getData()['caseUuid'] ?? null;
                    return
                        Rule::unique('covidcase', 'hpzone_number')
                        ->ignore($caseUuid, 'uuid')
                        ->whereNull('deleted_at');
            });
        $schema->add(StringType::createField('notes'))
            ->getValidationRules()
            ->addWarning('max:5000');

        $schema->add(ObjectType::createField('organisation', Organisation::class))
            ->setExternal()
            ->setReadOnly()
            ->setIncludedInEncode(false, EncodingContext::MODE_EXPORT)
            ->setEncoder(static function (EncodingContainer $container, ?Organisation $value): void {
                if ($value !== null) {
                    $container->uuid = $value->uuid;
                    $container->name = $value->name;
                } else {
                    $container->encodeNull();
                }
            });
        $schema->add(DateTimeType::createField('createdAt'))->setProxyForOwnerField('createdAt')->setReadOnly();
        $schema->add(DateTimeType::createField('deletedAt'))->setProxyForOwnerField('deletedAt')->setReadOnly();
        $schema->add(DateTimeType::createField('pairingAllowedUntil'))->setExternal()->setReadOnly();
        $schema->add(BoolType::createField('isPairingAllowed'))->setExternal()->setReadOnly();
        $schema->add(DateTimeType::createField('expiresAt'))->setExternal()->setReadOnly();

        // Fields up to version 2
        $schema->add(BoolType::createField('askedAboutCoronaMelder'))->setMaxVersion(1);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function getOrganisationFieldValue(): ?Organisation
    {
        $organisation = $this->getOwnerProxy()->organisation;
        if (!$organisation instanceof EloquentOrganisation) {
            return null;
        }

        return $organisation->toOrganisation();
    }

    protected function getPairingAllowedUntilFieldValue(): ?DateTimeInterface
    {
        $createdAt = $this->getOwnerProxy()->createdAt;
        if (!$createdAt instanceof DateTimeInterface) {
            return null;
        }

        $pairingAllowedInterval = (int) config('misc.case.pairingAllowedInterval');
        $pairingAllowedUntilTimestamp = $createdAt->getTimestamp() + $pairingAllowedInterval;

        try {
            return new DateTimeImmutable('@' . $pairingAllowedUntilTimestamp);
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function getIsPairingAllowedFieldValue(): bool
    {
        $pairingAllowedUntil = $this->getPairingAllowedUntilFieldValue();
        return $pairingAllowedUntil !== null && CarbonImmutable::now()->isBefore($pairingAllowedUntil);
    }
}
