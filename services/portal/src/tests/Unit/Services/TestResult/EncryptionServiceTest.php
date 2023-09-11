<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TestResult;

use App\Services\TestResult\EncryptionService;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use Tests\Unit\UnitTestCase;
use Throwable;

use function base64_encode;

final class EncryptionServiceTest extends UnitTestCase
{
    public function testDecryptPayload(): void
    {
        $payload = $this->faker->word();
        $unsealedData = $this->faker->word();

        $encryptionHelper = $this->createMock(EncryptionHelper::class);
        $encryptionHelper->expects($this->once())->method('unsealDataWithKey')
            ->with($this->anything(), SecurityModule::SK_TEST_RESULT)
            ->willReturn($unsealedData);

        $encryptionService = new EncryptionService($encryptionHelper);
        $actual = $encryptionService->decryptPayload(base64_encode($payload));

        $this->assertEquals($unsealedData, $actual);
    }

    public function testDecryptPayloadWithNon64EncodedString(): void
    {
        $encryptionService = new EncryptionService($this->createMock(EncryptionHelper::class));

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('payload can not be base64-decoded');
        $encryptionService->decryptPayload('a string with a non-base64 character, like: _');
    }
}
