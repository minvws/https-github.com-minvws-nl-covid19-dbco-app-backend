<?php

namespace Tests\Unit;

use App\Repositories\SessionStateRepository;
use Tests\TestCase;

class StateRepositoryTest extends TestCase
{
    public function testFieldCopy()
    {
        $repository = new SessionStateRepository();

        // Repo should start with an empty array.
        $fields = $repository->getCopiedFields( 'case', 'abcd');
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);

        // Mark a field as copied.
        $firstTime = $repository->markFieldAsCopied('case', 'abcd', 'firstname');
        $this->assertTrue($firstTime);

        $fields = $repository->getCopiedFields('case', 'abcd');

        $this->assertNotEmpty($fields);
        $this->assertContains('firstname', $fields);
        $this->assertCount(1, $fields);

        // Mark a second field as copied
        $firstTime = $repository->markFieldAsCopied('case', 'abcd', 'lastname');
        $this->assertFalse($firstTime);

        $fields = $repository->getCopiedFields('case', 'abcd');
        $this->assertCount(2, $fields);
        $this->assertContains('lastname', $fields);

        // Try to mark the same field again
        $firstTime = $repository->markFieldAsCopied('case', 'abcd', 'lastname');
        $this->assertFalse($firstTime);

        $fields = $repository->getCopiedFields('case', 'abcd');
        $this->assertCount(2, $fields);
        $this->assertContains('firstname', $fields);
        $this->assertContains('lastname', $fields);

        // Add a field for a different task and see if that doesn't effect the normal one
        $firstTime = $repository->markFieldAsCopied('case', 'efgh', 'firstname');
        $this->assertTrue($firstTime);

        $fields = $repository->getCopiedFields('case', 'abcd');
        $this->assertCount(2, $fields);
        $this->assertContains('firstname', $fields);
        $this->assertContains('lastname', $fields);

        // Clear the fields for the task and see if we're back to empty
        $repository->clearCopiedFields('case', 'abcd');
        $fields = $repository->getCopiedFields('case', 'abcd');
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
        $firstTime = $repository->markFieldAsCopied('case', 'abcd', 'lastname');
        $this->assertTrue($firstTime);

        // Shouldn't have affected the other one
        $fields = $repository->getCopiedFields('case', 'efgh');
        $this->assertCount(1, $fields);
        $this->assertContains('firstname', $fields);
        $firstTime = $repository->markFieldAsCopied('case', 'efgh', 'lastname');
        $this->assertFalse($firstTime);


    }
}
