<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Bsn;

use App\Repositories\Bsn\BsnServiceException;
use App\Repositories\Bsn\LocalBsnRepository;
use Tests\Unit\UnitTestCase;

class LocalBsnRepositoryTest extends UnitTestCase
{
    public function testGetByPseudoBsnGuid(): void
    {
        $pseudoBsnGuid = '1eaf0d45-1124-4799-931d-58f628635079';

        $localBsnRepository = new LocalBsnRepository();
        $pseudoBsn = $localBsnRepository->getByPseudoBsnGuid($pseudoBsnGuid, 'not_used');

        $this->assertEquals($pseudoBsnGuid, $pseudoBsn[0]->getGuid());
    }

    public function testGetByPseudoBsnGuidServiceNotAvailable(): void
    {
        $pseudoBsnGuid = '06A6B91C-D59B-401E-A5BF-4BF9262D85F8';

        $localBsnRepository = new LocalBsnRepository();

        $this->expectException(BsnServiceException::class);
        $this->expectExceptionMessage('Service not available (mocked)');
        $localBsnRepository->getByPseudoBsnGuid($pseudoBsnGuid, 'not_used');
    }

    public function testGetByPseudoBsnGuidServiceNotAvailableOn2ndRequest(): void
    {
        $pseudoBsnGuid = '8027C102-93EF-4735-AB66-97AA63B836EB';

        $localBsnRepository = new LocalBsnRepository();
        $pseudoBsn = $localBsnRepository->getByPseudoBsnGuid($pseudoBsnGuid, 'not_used');
        $this->assertEquals($pseudoBsnGuid, $pseudoBsn[0]->getGuid());

        $this->expectException(BsnServiceException::class);
        $this->expectExceptionMessage('Service not available: 2nd request (mocked)');
        $localBsnRepository->getByPseudoBsnGuid($pseudoBsnGuid, 'not_used');
    }
}
