<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Illuminate\Testing\PendingCommand;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class UnassignExpertQuestionsCommandTest extends FeatureTestCase
{
    public function testCommand(): void
    {
        $this->runCommand(0);
    }

    public function testCommandUnassignesAssignedQuestions(): void
    {
        $case = $this->createCaseForOrganisation($this->createOrganisation());
        $user = $this->createUser();

        $this->createExpertQuestionForCase($case);
        $this->createExpertQuestionForCase($case);
        $this->createExpertQuestionForCase($case, ['assigned_user_uuid' => $user->uuid]);
        $this->createExpertQuestionForCase($case, ['assigned_user_uuid' => $user->uuid]);

        $this->assertDatabaseCount('expert_question', 4);
        $this->assertDatabaseHas('expert_question', ['assigned_user_uuid' => $user->uuid]);

        $this->runCommand(2);

        $this->assertDatabaseCount('expert_question', 4);
        $this->assertDatabaseMissing('expert_question', ['assigned_user_uuid' => $user->uuid]);
    }

    private function runCommand(int $expectedCount): void
    {
        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('expert-questions:unassign');
        $artisan->expectsOutput('Unassigning assigned expert questions...')
            ->expectsOutput(sprintf('Unassigned %s expert questions', $expectedCount))
            ->assertExitCode(0)
            ->execute();
    }
}
