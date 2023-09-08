<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Purpose;
use App\Schema\Generator\JSONSchema\Diff\Schema\SubPurpose;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema-jsonschema-diff')]
class PurposeDiffTest extends UnitTestCase
{
    /**
     * Things we treat as unmodified.
     */
    public static function unmodifiedProvider(): array
    {
        return [
            'equal' => [
                new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose')),
                new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose')),
            ],
            'purpose-description-changed' => [
                new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose')),
                new Purpose('purpose1', 'Purpose 1', new SubPurpose('subPurpose1', 'Sub purpose')),
            ],
            'subpurpose-description-changed' => [
                new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose')),
                new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose 1')),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(Purpose $original, Purpose $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public function testDiffShouldReturnModified(): void
    {
        $original = new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose'));
        $new = new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose2', 'Sub purpose'));
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
    }

    public function testEncode(): void
    {
        $original = new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose1', 'Sub purpose'));
        $new = new Purpose('purpose1', 'Purpose', new SubPurpose('subPurpose2', 'Sub purpose'));
        $diff = $new->diff($original);

        $encoded = (new Encoder())->encode($diff);
        $this->assertEquals('modified', $encoded->diffType);
        $this->assertEquals($diff->original->identifier, $encoded->identifier);
        $this->assertEquals($diff->original->description, $encoded->description);
        $this->assertEquals($diff->new->subPurpose->identifier, $encoded->subPurpose->identifier);
        $this->assertEquals($diff->new->subPurpose->description, $encoded->subPurpose->description);
        $this->assertEquals($diff->original->subPurpose->identifier, $encoded->originalSubPurpose->identifier);
        $this->assertEquals($diff->original->subPurpose->description, $encoded->originalSubPurpose->description);
    }
}
