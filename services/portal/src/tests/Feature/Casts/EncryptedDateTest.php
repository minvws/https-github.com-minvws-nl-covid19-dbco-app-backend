<?php

declare(strict_types=1);

namespace Tests\Feature\Casts;

use App\Casts\EncryptedDate;
use App\Models\Eloquent\Person;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use ErrorException;
use Illuminate\Database\Eloquent\Model;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use Tests\Feature\FeatureTestCase;

class EncryptedDateTest extends FeatureTestCase
{
    public function testGetWhenValueNull(): void
    {
        $result = $this->getEncryptedDate(null);

        $this->assertNull($result);
    }

    public function testGetWhenValueEncrypted(): void
    {
        $date = CarbonImmutable::instance($this->faker->dateTime);

        $encryptionHelper = $this->app->get(EncryptionHelper::class);
        $dateEncrypted = $encryptionHelper->sealStoreValue(
            $date->serialize(),
            StorageTerm::short(),
            CarbonImmutable::now(),
        );

        $result = $this->getEncryptedDate($dateEncrypted);

        $this->assertTrue($date->equalTo($result));
    }

    public function testGetThrowsExceptionOnInvalidData(): void
    {
        $this->expectException(ErrorException::class);
        $this->getEncryptedDate($this->faker->word());
    }

    public function testSetWhenValueNull(): void
    {
        $result = $this->setEncryptedDate(new Person(), $this->faker->word(), null);

        $this->assertNull($result);
    }

    public function testSetWhenValueIsCarbonInstance(): void
    {
        $date = CarbonImmutable::instance($this->faker->dateTime);

        $person = new Person();
        $person->createdAt = $date;
        $key = $this->faker->word();

        $result = $this->setEncryptedDate($person, $key, $date);

        $this->assertEquals($date, $result['created_at']);
        $this->assertArrayHasKey($key, $result);

        $encryptionHelper = $this->app->get(EncryptionHelper::class);
        $unsealedSerializedDate = $encryptionHelper->unsealStoreValue($result[$key]);

        $this->assertTrue($date->equalTo(CarbonImmutable::fromSerialized($unsealedSerializedDate)));
    }

    private function getEncryptedDate(?string $value): ?CarbonImmutable
    {
        return (new EncryptedDate())->get(new Person(), $this->faker->word(), $value, []);
    }

    private function setEncryptedDate(Model $model, string $key, ?CarbonInterface $value): ?array
    {
        return (new EncryptedDate())->set($model, $key, $value, []);
    }
}
