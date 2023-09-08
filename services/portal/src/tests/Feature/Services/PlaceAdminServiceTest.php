<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Exceptions\PlaceVerificationException;
use App\Services\PlaceAdminService;
use Tests\Feature\FeatureTestCase;

class PlaceAdminServiceTest extends FeatureTestCase
{
    public function testVerifyPlaceWhenAlreadyVerified(): void
    {
        $place = $this->createPlace([
            'is_verified' => true,
        ]);

        /** @var PlaceAdminService $placeAdminService */
        $placeAdminService = $this->app->get(PlaceAdminService::class);

        $this->expectException(PlaceVerificationException::class);
        $this->expectExceptionMessage('Cannot verify an already verified place.');
        $placeAdminService->verifyPlace($place);
    }
}
