<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Eloquent\ExpertQuestion;
use App\Repositories\ExpertQuestionRepository;
use App\Services\ExpertQuestion\ExpertQuestionService;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function app;

#[Group('supervision')]
class ExpertQuestionServiceTest extends TestCase
{
    public function testGetExpertQuestionWithAuthorizationByCaseId(): void
    {
        $expertQuestion = new ExpertQuestion();
        $caseId = '7654321';
        $type = ExpertQuestionType::medicalSupervision();

        $this->mock(
            ExpertQuestionRepository::class,
            static function (MockInterface $mock) use ($expertQuestion, $caseId, $type): void {
                $mock->expects('getExpertQuestionByTypeAndCaseId')
                    ->with($caseId, $type)
                    ->andReturn($expertQuestion);
            },
        );

        $actual = app(ExpertQuestionService::class)->getExpertQuestionByTypeAndCaseId($caseId, $type);
        $this->assertEquals($expertQuestion, $actual);
    }
}
