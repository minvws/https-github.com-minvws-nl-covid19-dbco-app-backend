<?php
namespace DBCO\Worker\Application\Commands;

use DBCO\Worker\Application\Services\QuestionnaireService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshQuestionnairesCommand extends Command
{
    protected static $defaultName = 'questionnaire:refresh-all';

    /**
     * @var QuestionnaireService
     */
    private QuestionnaireService $questionnaireService;

    /**
     * Constructor.
     *
     * @param QuestionnaireService $questionnaireService
     */
    public function __construct(QuestionnaireService $questionnaireService)
    {
        parent::__construct();
        $this->questionnaireService = $questionnaireService;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Refresh questionnaires command')
            ->setHelp('Can be used to retrieve a fresh list of questionnaires from the health authority');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->questionnaireService->refreshQuestionnaires();
        return Command::SUCCESS;
    }
}
