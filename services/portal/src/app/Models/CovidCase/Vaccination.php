<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Shared\BaseVaccination;
use App\Models\Versions\CovidCase\Vaccination\VaccinationCommon;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1UpTo2;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV3Up;
use App\Models\Versions\Shared\VaccineInjection\VaccineInjectionCommon;
use App\Schema\Fields\Field;
use App\Schema\Schema;
use App\Schema\Types\SchemaType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use DateTimeInterface;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\VaccinationGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

use function app;
use function assert;
use function collect;
use function count;
use function is_array;

class Vaccination extends BaseVaccination implements VaccinationCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = parent::loadSchema();

        $schema->setCurrentVersion(3);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Vaccination');
        $schema->setDocumentationIdentifier('covidCase.vaccination');

        // Common fields
        /** @var Field<SchemaType> $isVaccinatedField */
        $isVaccinatedField = $schema->getCurrentVersion()
            ->getField('isVaccinated');

        $isVaccinatedField->getValidationRules()
            ->addOsirisFinished('required');

        // Fields up to version 1
        $schema->add(YesNoUnknown::getVersion(1)->createField('hasReceivedInvite'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addOsirisFinished('required');

        $schema->add(VaccinationGroup::getVersion(1)->createArrayField('groups'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addOsirisFinished(
                static function (ValidationContext $context): array {
                    if ($context->getValue('hasReceivedInvite') === YesNoUnknown::yes()->value) {
                        return ['required_without:otherGroup', 'array'];
                    }

                    return [];
                },
            );
        $schema->add(StringType::createField('otherGroup'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addWarning('max:500')
            ->addOsirisFinished(
                static function (ValidationContext $context): array {
                    if ($context->getValue('hasReceivedInvite') === YesNoUnknown::yes()->value) {
                        return ['required_without:groups', 'string'];
                    }

                    return [];
                },
            );

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function getInjectionDate(int $index): ?DateTimeInterface
    {
        assert($this instanceof VaccinationV1UpTo2 || $this instanceof VaccinationV3Up);
        if (!is_array($this->vaccineInjections)) {
            return null;
        }

        if (!isset($this->vaccineInjections[$index])) {
            return null;
        }

        return $this->vaccineInjections[$index]->injectionDate;
    }

    public function vaccinationCount(): int
    {
        if ($this instanceof VaccinationV1UpTo2) {
            return $this->vaccineInjections !== null ? count($this->vaccineInjections) : 0;
        }

        if ($this instanceof VaccinationV3Up) {
            return $this->vaccinationCount ?? 0;
        }

        return 0;
    }

    /**
     * @return Collection<VaccineInjectionCommon>
     */
    public function vaccineInjections(): Collection
    {
        assert($this instanceof VaccinationV1UpTo2 || $this instanceof VaccinationV3Up);
        return collect($this->vaccineInjections);
    }

    public function latestInjection(): ?VaccineInjectionCommon
    {
        if ($this->vaccinationCount() === 0 && $this->vaccineInjections()->count() === 0) {
            return null;
        }

        $latestVaccination = $this->vaccineInjections()->sortBy('injectionDate')->last();

        if ($latestVaccination === null) {
            return null;
        }

        Assert::isInstanceOf($latestVaccination, VaccineInjectionCommon::class);
        return $latestVaccination;
    }
}
