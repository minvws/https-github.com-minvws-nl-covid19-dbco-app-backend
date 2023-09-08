<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use App\Models\Versions\TestResult\General\GeneralV1;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Encryption\Security\CacheEntryNotFoundException;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

use function json_encode;
use function random_bytes;
use function sprintf;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

class TestResultTest extends FeatureTestCase
{
    private const SECURITY_CACHE_SECRET_KEY_NOT_FOUND = 'not_found';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(SecurityCache::class, static function (MockInterface $mock): void {
            $mock->expects('getSecretKey')
                ->with(self::SECURITY_CACHE_SECRET_KEY_NOT_FOUND)
                ->andThrow(CacheEntryNotFoundException::class);
            $mock->allows('getSecretKey')
                ->andReturn(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        });
    }

    public function testGetWhenSecretKeyNotFound(): void
    {
        $testResultSource = $this->faker->randomElement(TestResultSource::all());
        $typeOfTest = $this->faker->optional()->randomElement(TestResultTypeOfTest::all());

        // create test result
        $testResult = $this->createTestResult([
            'source' => $testResultSource,
            'type_of_test' => $typeOfTest,
        ]);
        $testResult->general = GeneralV1::newInstanceWithVersion(1, function (GeneralV1 $generalV1): void {
            $generalV1->testLocation = $this->faker->word();
        });
        $testResult->save();

        // overwrite general fragment with expired key to trigger CacheEntryNotFoundException
        $expiredFragmentData = json_encode([
            'ciphertext' => json_encode([]),
            'nonce' => 'foo',
            'key' => self::SECURITY_CACHE_SECRET_KEY_NOT_FOUND,
        ]);
        DB::update(
            sprintf("UPDATE `test_result_fragment` SET `data` = '%s' WHERE `test_result_id` = '%s'", $expiredFragmentData, $testResult->id),
        );

        // refresh to force reload from database
        $testResult->refresh();

        // this should still work, though null instead of the saved location (since keys have expired)
        $this->assertNull($testResult->general->testLocation);

        // not encrypted fields should still have the correct value
        $this->assertEquals($testResultSource, $testResult->source);
        $this->assertEquals($typeOfTest, $testResult->type_of_test);
    }
}
