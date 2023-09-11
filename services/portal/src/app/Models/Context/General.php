<?php

declare(strict_types=1);

namespace App\Models\Context;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\Moment as EloquentMoment;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Context\General\GeneralCommon;
use App\Models\Versions\Context\Moment\MomentCommon;
use App\Repositories\MomentRepository;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use Closure;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\Log;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use Webmozart\Assert\Assert;

use function app;
use function is_array;
use function is_null;

class General extends FragmentCompat implements GeneralCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Context\\General');
        $schema->setDocumentationIdentifier('context.general');

        $schema->add(StringType::createField('label'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addWarning('max:250');
        $schema->add(ContextRelationship::getVersion(1)->createField('relationship'))
            ->setProxyForOwnerField();
        $schema->add(StringType::createField('otherRelationship'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(StringType::createField('remarks'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addWarning('max:5000');
        $schema->add(StringType::createField('note'))
            ->setProxyForOwnerField('explanation')
            ->getValidationRules()
            ->addWarning('max:5000');
        $schema->add(BoolType::createField('isSource'))
            ->setProxyForOwnerField();
        $schema->add(Moment::getSchema()->getVersion(1)->createArrayField('moments')->setExternal())
            ->getValidationRules()
            ->addFatal(static function (ValidationContext $context) {
                Assert::isInstanceOf($context->getValue('context'), Context::class);
                if (is_null($context->getValue('context')->case)) {
                    return ['prohibited'];
                }
            });

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function getMomentsFieldValue(Closure $getter): ?array
    {
        if (!$this->getOwnerProxy()->moments) {
            return [];
        }

        $moments = [];
        foreach ($this->getOwnerProxy()->moments as $item) {
            $moment = Moment::newInstanceWithVersion(1);
            $moment->day = $item->day;
            $moment->startTime = $this->formatTime($item->start_time);
            $moment->endTime = $this->formatTime($item->end_time);
            $moments[] = $moment;
        }
        return $moments;
    }

    protected function setMomentsFieldValue(?array $moments, Closure $setter): void
    {
        $momentRepository = app(MomentRepository::class);
        $momentRepository->deleteAllMomentsByContext($this->getOwnerProxy()->uuid);

        if (!is_array($moments)) {
            return;
        }

        /** @var Context $context */
        $context = $this->getOwnerProxy()->getOwner();

        /** @var MomentCommon $input */
        foreach ($moments as $input) {
            $moment = new EloquentMoment();
            $moment->day = $input->day;
            $moment->start_time = $this->formatTime($input->startTime);
            $moment->end_time = $this->formatTime($input->endTime);

            $context->moments()->save($moment);
        }

        $context->load('moments');
    }

    /**
     * @throws Exception
     */
    private function formatTime(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        if ($this->isValidDate($input, 'H:i:s') || $this->isValidDate($input, 'H:i')) {
            return (new DateTimeImmutable($input))->format('H:i');
        }

        Log::error('Invalid time format found for moment', [
            'time' => $input,
        ]);

        return null;
    }

    private function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateObj = DateTimeImmutable::createFromFormat($format, $date);
        return $dateObj && $dateObj->format($format) === $date;
    }
}
