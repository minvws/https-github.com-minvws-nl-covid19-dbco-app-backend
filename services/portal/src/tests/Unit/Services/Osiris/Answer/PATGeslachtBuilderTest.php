<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\PATGeslachtBuilder;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\Gender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(PATGeslachtBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class PATGeslachtBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function genderProvider(): Generator
    {
        yield 'Male' => [Gender::male(), 'M'];
        yield 'Female' => [Gender::female(), 'V'];
        yield 'Other' => [Gender::other(), 'Onb'];
        yield 'null' => [null, 'Onb'];
    }

    #[DataProvider('genderProvider')]
    public function testGender(?Gender $gender, string $value): void
    {
        $case = $this->createCase($gender);
        $this->answersForCase($case)->assertAnswer(new Answer('PATGeslacht', $value));
    }

    private function createCase(?Gender $gender): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->index->gender = $gender;
        return $case;
    }
}
