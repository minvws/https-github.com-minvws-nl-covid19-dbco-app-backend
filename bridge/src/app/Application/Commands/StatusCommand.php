<?php
namespace DBCO\Bridge\Application\Commands;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Predis\Client as PredisClient;

/**
 * Status check.
 *
 * @package DBCO\Bridge\Application\Commands
 */
class StatusCommand extends Command
{
    protected static $defaultName = 'status';

    /**
     * @var PredisClient
     */
    private PredisClient $redisClient;

    /**
     * @var GuzzleClient
     */
    private GuzzleClient $healthAuthorityGuzzleClient;

    /**
     * Constructor.
     */
    public function __construct(PredisClient $redisClient, GuzzleClient $healthAuthorityGuzzleClient)
    {
        parent::__construct();
        $this->setDescription("Check status of dependencies");
        $this->redisClient = $redisClient;
        $this->healthAuthorityGuzzleClient = $healthAuthorityGuzzleClient;
    }

    /**
     * Check status using the given callback.
     *
     * @param string   $label
     * @param callable $callback
     *
     * @return bool
     */
    private function checkStatus(string $label, OutputInterface $output, callable $callback): bool
    {
        $result = false;

        $output->write("Checking $label status...");

        try {
            $result = $callback();
        } catch (Exception $e) {
        }

        $output->writeln(' [' . ($result ? 'OK' : 'ERROR') . ']');

        return $result;
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redisOK =$this->checkStatus('Redis', $output, fn () => (string)$this->redisClient->ping() === 'PONG');
        $haaOK = $this->checkStatus('Health Authority API', $output, function () {
            $response = $this->healthAuthorityGuzzleClient->get('ping');
            return $response->getStatusCode() === 200 && (string)$response->getBody() === 'PONG';
        });

        return $redisOK && $haaOK ? Command::SUCCESS : Command::FAILURE;
    }
}
