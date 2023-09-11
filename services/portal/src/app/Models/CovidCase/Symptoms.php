<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Eloquent\EloquentCase;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Symptoms\SymptomsCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use Closure;
use Illuminate\Database\Eloquent\Model;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;
use function count;
use function in_array;
use function is_array;
use function json_decode;

class Symptoms extends FragmentCompat implements SymptomsCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Symptoms');
        $schema->setDocumentationIdentifier('covidCase.symptoms');

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasSymptoms'));
        $schema->add(Symptom::getVersion(1)->createArrayField('symptoms'));
        $schema->add(StringType::createArrayField('otherSymptoms'))
            ->getElementValidationRules()
            ->addFatal('max:5000');
        $schema->add(YesNoUnknown::getVersion(1)->createField('wasSymptomaticAtTimeOfCall'))->setMaxVersion(1);
        $schema->add(DateTimeType::createField('stillHadSymptomsAt', 'Y-m-d'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addWarning('date_format:Y-m-d')
            ->addWarning('before_or_equal:today');
        $schema->add(StringType::createField('diseaseCourse'))
            ->getValidationRules()
            ->addWarning('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setHasSymptomsFieldValue(?YesNoUnknown $value, Closure $setter): void
    {
        $setter($value);

        if ($value === null || $value === YesNoUnknown::unknown()) {
            $this->getOwnerProxy()->symptomatic = null;
        } else {
            $this->getOwnerProxy()->symptomatic = $value === YesNoUnknown::yes();
        }
    }

    protected function postLoad(Model $model, array $attributes): void
    {
        /** @var EloquentCase $case */
        $case = $model;

        if ($case->index_submitted_at === null) {
            return;
        }

        $indexSubmittedSymptoms = $case->index_submitted_symptoms;
        if ($indexSubmittedSymptoms === null) {
            $this->hasSymptoms = $this->hasSymptoms ?? YesNoUnknown::no();
            return;
        }

        $indexSubmittedSymptoms = @json_decode($indexSubmittedSymptoms);
        if (!is_array($indexSubmittedSymptoms)) {
            $this->hasSymptoms = $this->hasSymptoms ?? YesNoUnknown::no();
            return;
        }

        if ($this->symptoms === null) {
            $this->symptoms = Symptom::tryFromArray($indexSubmittedSymptoms);
        } elseif (count($indexSubmittedSymptoms) > 0) {
            $symptoms = $indexSubmittedSymptoms;
            foreach ($this->symptoms as $symptom) {
                if (!in_array($symptom->value, $symptoms, true)) {
                    $symptoms[] = $symptom->value;
                }
            }
            $this->symptoms = Symptom::tryFromArray($symptoms);
        }

        if ($this->hasSymptoms === null) {
            $this->hasSymptoms = !empty($this->symptoms) ? YesNoUnknown::yes() : YesNoUnknown::no();
        }
    }
}
