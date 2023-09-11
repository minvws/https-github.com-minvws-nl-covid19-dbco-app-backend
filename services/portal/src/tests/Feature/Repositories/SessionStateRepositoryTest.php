<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Repositories\SessionStateRepository;
use Tests\Feature\FeatureTestCase;

class SessionStateRepositoryTest extends FeatureTestCase
{
    private SessionStateRepository $sessionStateRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionStateRepository = $this->app->get(SessionStateRepository::class);
    }

    public function testFieldCopy(): void
    {
        // Repo should start with an empty array.
        $fields = $this->sessionStateRepository->getCopiedFields('case', 'abcd');
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);

        // Mark a field as copied.
        $firstTime = $this->sessionStateRepository->markFieldAsCopied('case', 'abcd', 'firstname');
        $this->assertTrue($firstTime);

        $fields = $this->sessionStateRepository->getCopiedFields('case', 'abcd');

        $this->assertNotEmpty($fields);
        $this->assertContains('firstname', $fields);
        $this->assertCount(1, $fields);

        // Mark a second field as copied
        $firstTime = $this->sessionStateRepository->markFieldAsCopied('case', 'abcd', 'lastname');
        $this->assertFalse($firstTime);

        $fields = $this->sessionStateRepository->getCopiedFields('case', 'abcd');
        $this->assertCount(2, $fields);
        $this->assertContains('lastname', $fields);

        // Try to mark the same field again
        $firstTime = $this->sessionStateRepository->markFieldAsCopied('case', 'abcd', 'lastname');
        $this->assertFalse($firstTime);

        $fields = $this->sessionStateRepository->getCopiedFields('case', 'abcd');
        $this->assertCount(2, $fields);
        $this->assertContains('firstname', $fields);
        $this->assertContains('lastname', $fields);

        // Add a field for a different task and see if that doesn't effect the normal one
        $firstTime = $this->sessionStateRepository->markFieldAsCopied('case', 'efgh', 'firstname');
        $this->assertTrue($firstTime);

        $fields = $this->sessionStateRepository->getCopiedFields('case', 'abcd');
        $this->assertCount(2, $fields);
        $this->assertContains('firstname', $fields);
        $this->assertContains('lastname', $fields);

        // Clear the fields for the task and see if we're back to empty
        $this->sessionStateRepository->clearCopiedFields('case', 'abcd');
        $fields = $this->sessionStateRepository->getCopiedFields('case', 'abcd');
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
        $firstTime = $this->sessionStateRepository->markFieldAsCopied('case', 'abcd', 'lastname');
        $this->assertTrue($firstTime);

        // Shouldn't have affected the other one
        $fields = $this->sessionStateRepository->getCopiedFields('case', 'efgh');
        $this->assertCount(1, $fields);
        $this->assertContains('firstname', $fields);
        $firstTime = $this->sessionStateRepository->markFieldAsCopied('case', 'efgh', 'lastname');
        $this->assertFalse($firstTime);
    }

    public function testGetCopiedFieldsWithInvalidSessionValue(): void
    {
        $this->session(['storedFields' => $this->faker->word()]);

        $copiedFields = $this->sessionStateRepository->getCopiedFields('case', null);
        $this->assertEquals([], $copiedFields);
    }
}
