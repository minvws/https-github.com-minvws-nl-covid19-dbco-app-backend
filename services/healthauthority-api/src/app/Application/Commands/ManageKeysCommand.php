<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use Exception;
use Predis\Client as PredisClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Continuous process that has the following responsibilities:
 * - Make sure all necessary keys exist in the HSM.
 * - Make sure all necessary keys exist in the cache.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Commands
 */
class ManageKeysCommand extends Command
{
    protected static $defaultName = 'security:manage-keys';

    /**
     * @var SecurityService
     */
    private SecurityService $securityService;

    /**
     * @var PredisClient
     */
    private PredisClient $redisClient;

    /**
     * Constructor.
     *
     * @param SecurityService $securityService
     */
    public function __construct(SecurityService $securityService, PredisClient $redisClient)
    {
        parent::__construct();
        $this->securityService = $securityService;
        $this->redisClient = $redisClient;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Continuous manage security module keys')
            ->setHelp('Continuous process for creating, caching and rotating security module keys');
    }

    /**
     * Invoke the given closure and output the result.
     *
     * @param string          $label
     * @param OutputInterface $output
     * @param Closure         $closure
     */
    private function invoke(string $label, OutputInterface $output, Closure $closure)
    {
        $output->write($label . '... ');

        try {
            $result = $closure();

            if (is_bool($result)) {
                $result = $result ? 'OK' : 'FAILED';
            } else if (!is_string($result)) {
                $result = 'OK';
            }

            $output->writeln('[' . strtoupper($result) . ']');
            return $result;
        } catch (Exception $e) {
            $output->writeln('[ERROR]');
            $output->writeln('ERROR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create or load key exchange secret key.
     *
     * @return string
     */
    private function createOrLoadKeyExchangeSecretKey(): string
    {
        $result = $this->securityService->createKeyExchangeSecretKey();
        return $result ? 'CREATED' : 'LOADED';
    }

    /**
     * Manage store secret keys.
     *
     * @param OutputInterface        $output
     * @param DateTimeInterface|null $previousCurrentDay
     *
     * @return DateTimeInterface Current day.
     */
    private function manageStoreSecretKeys(OutputInterface $output, ?DateTimeInterface $previousCurrentDay = null): DateTimeInterface
    {
        try {
            return $this->securityService->manageStoreSecretKeys(
                function (DateTimeImmutable $day, string $mutation, ?Exception $exception = null) use ($output) {
                    $output->writeln(
                        sprintf(
                            'Manage store key for day %s... [%s]',
                            $day->format('Y-m-d'),
                            strtoupper($mutation)
                        )
                    );

                    if ($exception !== null) {
                        $output->writeln('ERROR: ' . $exception->getMessage());
                    }
                },
                $previousCurrentDay
            );
        } catch (Exception $e) {
            $output->writeln('ERROR: ' . $e->getMessage());
        }
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
        $result = $this->invoke('Create/load key exchange key', $output, fn () => $this->createOrLoadKeyExchangeSecretKey());
        if ($result !== false) {
            $output->writeln('Public key exchange key: ' . base64_encode($this->securityService->getKeyExchangePublicKey()));
        }

        $currentDay = null;

        while (true) {
            $currentDay = $this->manageStoreSecretKeys($output, $currentDay);
            $this->invoke('Cache keys in memory', $output, fn () => $this->securityService->cacheKeys());
            $this->redisClient->disconnect(); // force disconnect so a new connection is established later on
            sleep(60);
        }

        return Command::SUCCESS;
    }
}
