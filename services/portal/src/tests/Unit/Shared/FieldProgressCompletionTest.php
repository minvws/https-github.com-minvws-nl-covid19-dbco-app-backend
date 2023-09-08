<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use InvalidArgumentException;
use MinVWS\DBCO\Metrics\Services\FieldProgressCompletion;
use Tests\Unit\UnitTestCase;

final class FieldProgressCompletionTest extends UnitTestCase
{
    public function testIsComplete(): void
    {
        $fieldProgressCompletion = FieldProgressCompletion::create(['field1', 'field2']);
        $fieldProgressCompletion->completeCheck('field1', "");
        $fieldProgressCompletion->completeCheck('field2', 'a value');

        $this->assertTrue($fieldProgressCompletion->isComplete('field2'));
        $this->assertFalse($fieldProgressCompletion->isComplete('field1', 'field2'));
        $this->assertFalse($fieldProgressCompletion->isComplete('field1'));
        $this->assertFalse($fieldProgressCompletion->isComplete());

        $fieldProgressCompletion->completeCheck('field1', 'a value');
        $this->assertTrue($fieldProgressCompletion->isComplete('field1', 'field2'));
        $this->assertTrue($fieldProgressCompletion->isComplete('field1'));
        $this->assertTrue($fieldProgressCompletion->isComplete());
    }

    public function testCompleteCheckOnNonExistingFieldShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $fieldProgressCompletion = FieldProgressCompletion::create(['field1', 'field2']);
        $fieldProgressCompletion->completeCheck('field3', null);
    }

    public function testFieldAlreadyCompleteShouldRemainComplete(): void
    {
        $fieldProgressCompletion = FieldProgressCompletion::create(['field1']);
        $fieldProgressCompletion->completeCheck('field1', 'some value');
        $fieldProgressCompletion->completeCheck('field1', '');
        $this->assertTrue($fieldProgressCompletion->isComplete());
    }
}
