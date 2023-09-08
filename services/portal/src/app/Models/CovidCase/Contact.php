<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Contact\ContactCommon;
use App\Observers\ContactSearchHashObserver;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

use function app;
use function in_array;
use function request;

class Contact extends AbstractCovidCaseFragment implements ContactCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Contact');
        $schema->setDocumentationIdentifier('covidCase.contact');

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
            ->addWarning('email:filter')
            ->addWarning('max:250');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(ContactSearchHashObserver::class);
    }

    public function getPhoneAttribute(): ?string
    {
        $phone = $this->assignedFieldValue('phone');
        Assert::nullOrString($phone);
        return $this->formatPhone($phone);
    }

    public function setPhoneAttribute(?string $phone): void
    {
        $this->assignFieldValue('phone', $this->formatPhone($phone));
    }

    private function formatPhone(?string $phone): ?string
    {
        return $phone !== null ? PhoneFormatter::format($phone) : null;
    }
}
