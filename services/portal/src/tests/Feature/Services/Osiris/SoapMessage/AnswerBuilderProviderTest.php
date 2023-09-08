<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris\SoapMessage;

use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Osiris\Answer\Builder;
use App\Services\Osiris\SoapMessage\AnswerBuilderProvider;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;
use Generator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function count;

#[Group('osiris')]
class AnswerBuilderProviderTest extends FeatureTestCase
{
    #[DataProvider('provideFilterAttributes')]
    public function testGetAll(
        ?QuestionnaireVersion $version,
        ?CaseExportType $type,
        array $expectedBuilders,
    ): void {
        $provider = new AnswerBuilderProvider($this->getMockAnswerBuildersConfig(
            Mockery::namedMock('NoVersionTypeInitialDefinitive', Builder::class),
            Mockery::namedMock('Version9TypeDefinitive', Builder::class),
            Mockery::namedMock('Version9TypeInitialDefinitiveDeleted', Builder::class),
            Mockery::namedMock('Version10TypeDefinitiveDeleted', Builder::class),
            Mockery::namedMock('Version10TypeDeleted', Builder::class),
            Mockery::namedMock('Version910TypeInitial', Builder::class),
            Mockery::namedMock('Version910TypeInitialDeleted', Builder::class),
            Mockery::namedMock('Version910NoTypes', Builder::class),
        ));

        $builders = $provider->getAll($version, $type);

        $this->assertCount(count($expectedBuilders), $builders);
        $this->assertEquals($expectedBuilders, $builders->map(static fn ($builder) => $builder::class)->toArray());
        foreach ($builders as $builder) {
            $this->assertInstanceOf(Builder::class, $builder);
        }
    }

    public static function provideFilterAttributes(): Generator
    {
        yield 'no filters' => [
            null,
            null,
            [
                'NoVersionTypeInitialDefinitive',
                'Version9TypeDefinitive',
                'Version9TypeInitialDefinitiveDeleted',
                'Version10TypeDefinitiveDeleted',
                'Version10TypeDeleted',
                'Version910TypeInitial',
                'Version910TypeInitialDeleted',
                'Version910NoTypes',
            ],
        ];
        yield 'questionnaire filter' => [
            QuestionnaireVersion::V10,
            null,
            [
                'Version10TypeDefinitiveDeleted',
                'Version10TypeDeleted',
                'Version910TypeInitial',
                'Version910TypeInitialDeleted',
                'Version910NoTypes',
            ],
        ];
        yield 'case export type filter' => [
            null,
            CaseExportType::INITIAL_ANSWERS,
            [
                'NoVersionTypeInitialDefinitive',
                'Version9TypeInitialDefinitiveDeleted',
                'Version910TypeInitial',
                'Version910TypeInitialDeleted',
            ],
        ];
        yield 'questionnaire and case export type filter' => [
            QuestionnaireVersion::V9,
            CaseExportType::DELETED_STATUS,
            [
                'Version9TypeInitialDefinitiveDeleted',
                'Version910TypeInitialDeleted',
            ],
        ];
    }

    private function getMockAnswerBuildersConfig(
        Builder $noVersionTypeInitialDefinitive,
        Builder $version9TypeDefinitive,
        Builder $version9TypeInitialDefinitiveDeleted,
        Builder $version10TypeDefinitiveDeleted,
        Builder $version10TypeDeleted,
        Builder $version910TypeInitial,
        Builder $version910TypeInitialDeleted,
        Builder $version910NoTypes,
    ): array {
        return [
            $noVersionTypeInitialDefinitive::class => [
                'questionnaireVersion' => [],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            $version9TypeDefinitive::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::DEFINITIVE_ANSWERS],
            ],
            $version9TypeInitialDefinitiveDeleted::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS, CaseExportType::DELETED_STATUS],
            ],
            $version10TypeDefinitiveDeleted::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::DEFINITIVE_ANSWERS, CaseExportType::DELETED_STATUS],
            ],
            $version10TypeDeleted::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::DELETED_STATUS],
            ],
            $version910TypeInitial::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS],
            ],
            $version910TypeInitialDeleted::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DELETED_STATUS],
            ],
            $version910NoTypes::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [],
            ],
        ];
    }
}
