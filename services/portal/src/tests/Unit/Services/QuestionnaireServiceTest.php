<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Questionnaire;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\QuestionRepository;
use App\Services\QuestionnaireService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\UnitTestCase;

class QuestionnaireServiceTest extends UnitTestCase
{
    public function testGetQuestionnaire(): void
    {
        $questionnaireUuid = $this->faker->uuid();

        /** @var QuestionRepository&MockObject $questionRepository */
        $questionRepository = Mockery::mock(
            QuestionRepository::class,
            static function (MockInterface $mock) use ($questionnaireUuid): void {
                $mock->expects('getQuestions')
                    ->with($questionnaireUuid)
                    ->andReturn(new Collection());
            },
        );

        /** @var QuestionnaireRepository&MockObject $questionnaireRepository */
        $questionnaireRepository = Mockery::mock(
            QuestionnaireRepository::class,
            static function (MockInterface $mock) use ($questionnaireUuid): void {
                $questionnaire = new Questionnaire();
                $questionnaire->uuid = $questionnaireUuid;
                $mock->expects('getQuestionnaire')
                    ->andReturn($questionnaire);
            },
        );

        $questionnaireService = new QuestionnaireService($questionRepository, $questionnaireRepository);
        $questionnaire = $questionnaireService->getQuestionnaire($this->faker->word());

        $this->assertEquals($questionnaireUuid, $questionnaire->uuid);
        $this->assertEmpty($questionnaire->questions);
    }

    public function testGetQuestionnaireWithUnknownTaskType(): void
    {
        /** @var QuestionRepository&MockObject $questionRepository */
        $questionRepository = Mockery::mock(QuestionRepository::class);

        /** @var QuestionnaireRepository&MockObject $questionnaireRepository */
        $questionnaireRepository = Mockery::mock(
            QuestionnaireRepository::class,
            static function (MockInterface $mock): void {
                $mock->expects('getQuestionnaire')
                    ->andReturn(null);
            },
        );

        $questionnaireService = new QuestionnaireService($questionRepository, $questionnaireRepository);
        $questionnaire = $questionnaireService->getQuestionnaire($this->faker->word());

        $this->assertNull($questionnaire);
    }

    public function testGetLatestQuestionnaire(): void
    {
        $questionnaireUuid = $this->faker->uuid();

        /** @var QuestionRepository&MockObject $questionRepository */
        $questionRepository = Mockery::mock(
            QuestionRepository::class,
            static function (MockInterface $mock) use ($questionnaireUuid): void {
                $mock->expects('getQuestions')
                    ->with($questionnaireUuid)
                    ->andReturn(new Collection());
            },
        );

        /** @var QuestionnaireRepository&MockObject $questionnaireRepository */
        $questionnaireRepository = Mockery::mock(
            QuestionnaireRepository::class,
            static function (MockInterface $mock) use ($questionnaireUuid): void {
                $questionnaire = new Questionnaire();
                $questionnaire->uuid = $questionnaireUuid;
                $mock->expects('getLatestQuestionnaire')
                    ->andReturn($questionnaire);
            },
        );

        $questionnaireService = new QuestionnaireService($questionRepository, $questionnaireRepository);
        $questionnaire = $questionnaireService->getLatestQuestionnaire($this->faker->word());

        $this->assertEquals($questionnaireUuid, $questionnaire->uuid);
        $this->assertEmpty($questionnaire->questions);
    }

    public function testGetLatestQuestionnaireWithUnknownTaskType(): void
    {
        /** @var QuestionRepository&MockObject $questionRepository */
        $questionRepository = Mockery::mock(QuestionRepository::class);

        /** @var QuestionnaireRepository&MockObject $questionnaireRepository */
        $questionnaireRepository = Mockery::mock(
            QuestionnaireRepository::class,
            static function (MockInterface $mock): void {
                $mock->expects('getLatestQuestionnaire')
                    ->andReturn(null);
            },
        );

        $questionnaireService = new QuestionnaireService($questionRepository, $questionnaireRepository);
        $questionnaire = $questionnaireService->getLatestQuestionnaire($this->faker->word());

        $this->assertNull($questionnaire);
    }
}
