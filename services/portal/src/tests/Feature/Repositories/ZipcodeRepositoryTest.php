<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Repositories\ZipcodeRepository;
use Tests\Feature\FeatureTestCase;

class ZipcodeRepositoryTest extends FeatureTestCase
{
    private ZipcodeRepository $zipcodeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zipcodeRepository = $this->app->get(ZipcodeRepository::class);
    }

    public function testFindByZipcode(): void
    {
        $zipcode = $this->createZipcode();

        $result = $this->zipcodeRepository->findByZipcode($zipcode->zipcode);

        $this->assertEquals($zipcode->toArray(), $result->toArray());
    }

    public function testFindByZipcodeNotFound(): void
    {
        $result = $this->zipcodeRepository->findByZipcode($this->faker->postcode);

        $this->assertNull($result);
    }
}
