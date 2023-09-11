<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Responses\Context;

use App\Http\Responses\Context\ContextEncoder;
use MinVWS\Codable\EncodingContainer;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\TestCase;

use function property_exists;

class ContextEncoderTest extends TestCase
{
    public function testEncodeWithNonInstance(): void
    {
        /** @var EncodingContainer&MockObject $encodingContainer */
        $encodingContainer = $this->mock(EncodingContainer::class, static function (MockInterface $mock): void {
            $mock->expects('nestedContainer')->never();
        });

        $contextEncoder = new ContextEncoder();
        $nonContextInstance = new stdClass();
        $contextEncoder->encode($nonContextInstance, $encodingContainer);

        $this->assertFalse(property_exists($nonContextInstance, 'uuid'));
    }
}
