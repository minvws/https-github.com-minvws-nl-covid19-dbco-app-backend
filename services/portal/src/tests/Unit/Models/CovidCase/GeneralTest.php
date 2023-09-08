<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\General;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('fragment')]
final class GeneralTest extends TestCase
{
    use ValidatesModels;

    public function testEncodeDecode(): void
    {
        /** @var General $general */
        $general = General::getSchema()->getCurrentVersion()->newInstance();
        $general->reference = "Dr Strange";

        $encoded = (new Encoder())->encode($general);

        /** @var General $decoded */
        $decoded = (new Decoder())->decode($encoded)->decodeObject(General::class);

        $this->assertEquals($general->jsonSerialize(), $decoded->jsonSerialize());
    }

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(General::class, []);

        $this->assertArrayHasKey('reference', $validationResult['fatal']['failed']);
    }
}
