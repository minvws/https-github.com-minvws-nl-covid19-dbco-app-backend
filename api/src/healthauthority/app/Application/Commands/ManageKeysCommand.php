<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use Closure;
use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Continuous process that has the following responsibilities:
 * - Make sure all necessary keys exist in the HSM.
 * - Make sure all necessary keys exist in the cache.
 * - Rotate keys (TODO!)
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
     * Constructor.
     *
     * @param SecurityService $securityService
     */
    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
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
     * Create or load data store secret key.
     *
     * @return string
     */
    private function createOrLoadStoreSecretKey(): string
    {
        $result = $this->securityService->createStoreSecretKey();
        return $result ? 'CREATED' : 'LOADED';
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

        $this->invoke('Create/load data store key', $output, fn () => $this->createOrLoadStoreSecretKey());

        while (true) {
            $this->invoke('Cache keys in memory', $output, fn () => $this->securityService->cacheKeys());
            sleep(60);
        }

        return Command::SUCCESS;
    }
}
