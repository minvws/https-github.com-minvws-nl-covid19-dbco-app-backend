<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\ExpertQuestionRepository;
use Illuminate\Console\Command;

use function sprintf;

class UnassignExpertQuestionsCommand extends Command
{
    /** @var string */
    protected $signature = 'expert-questions:unassign';

    /** @var string */
    protected $description = 'Unassigns all assigned expert questions so they can be picked up again';

    public function handle(
        ExpertQuestionRepository $expertQuestionRepository,
    ): int {
        $this->info('Unassigning assigned expert questions...');

        $count = $expertQuestionRepository->unassignAllAssignedExpertQuestions();

        $this->info(sprintf('Unassigned %s expert questions', $count));

        return Command::SUCCESS;
    }
}
