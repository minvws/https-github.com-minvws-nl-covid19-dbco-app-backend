<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SecurityService;
use Closure;
use Illuminate\Console\Command;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Encryption\Security\StorageTermUnit;
use Predis\PredisException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Webmozart\Assert\Assert;

use function base64_encode;
use function is_bool;
use function is_string;
use function sleep;
use function sprintf;
use function strtoupper;

/**
 * Continuous process that has the following responsibilities:
 * - Make sure all necessary keys exist in the HSM.
 * - Make sure all necessary keys exist in the cache.
 */
#[AsCommand(name: 'security:manage-keys', description: 'Continuous manage security module keys')]
class ManageKeysCommand extends Command
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();

        $this->securityService = $securityService;
    }

    protected function configure(): void
    {
        $this->setHelp('Continuous process for creating, caching and rotating security module keys')
            ->addOption(
                'createMissingPastKeys',
                'p',
                InputOption::VALUE_NONE,
                'Create keys that are in the past, but are missing, useful for environments that generate test data',
            )
            ->addOption(
                'singleRun',
                's',
                InputOption::VALUE_NONE,
                'Single run, only create/load keys once and quit immediately afterwards',
            );
    }

    private function invoke(string $label, OutputInterface $output, Closure $closure): string | bool
    {
        $output->write($label . '... ');

        try {
            $result = $closure();

            if (is_bool($result)) {
                $result = $result ? 'OK' : 'FAILED';
            } elseif (!is_string($result)) {
                $result = 'OK';
            }

            $output->writeln('[' . strtoupper($result) . ']');
            return $result;
        } catch (Throwable $e) {
            $output->writeln('[ERROR]');
            $output->writeln('ERROR: ' . $e->getMessage());
            return false;
        }
    }

    private function createOrLoadKey(string $keyName): string
    {
        $result = $this->securityService->createKey($keyName);

        return $result ? 'CREATED' : 'LOADED';
    }

    /**
     * @throws PredisException
     * @throws Throwable
     */
    private function manageStoreSecretKeys(
        OutputInterface $output,
        StorageTerm $term,
        ?StorageTermUnit $previousCurrentUnit,
        bool $createMissingPastKeys = false,
    ): StorageTermUnit {
        try {
            return $this->securityService->manageStoreSecretKeys(
                $term,
                $previousCurrentUnit,
                static function (StorageTermUnit $unit, string $mutation, ?Throwable $exception = null) use ($output, $term): void {
                    $output->writeln(
                        sprintf(
                            'Manage %s term storage key "%s"... [%s]',
                            $term,
                            $unit,
                            strtoupper($mutation),
                        ),
                    );

                    if ($exception === null) {
                        return;
                    }

                    $output->writeln('ERROR: ' . $exception->getMessage());
                    if ($exception instanceof PredisException) {
                        // fatal error
                        throw $exception;
                    }
                },
                $createMissingPastKeys,
            );
        } catch (Throwable $e) {
            $output->writeln('FATAL ERROR: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $createMissingPastKeys = (bool) $input->getOption('createMissingPastKeys');
        $singleRun = $input->getOption('singleRun');

        foreach (SecurityModule::SK_DEFAULT_KEYS as $key) {
            $result = $this->invoke(
                sprintf('Create/load key - %s', $key),
                $output,
                fn () => $this->createOrLoadKey($key),
            );
            if ($result === false) {
                continue;
            }

            $publicKey = $this->securityService->getPublicKey($key);
            Assert::string($publicKey);
            $output->writeln(
                sprintf('Public key - %s : %s', $key, base64_encode($publicKey)),
            );
        }

        $currentVeryShortTermUnit = null;
        $currentShortTermUnit = null;
        $currentLongTermUnit = null;

        while (true) {
            try {
                $currentVeryShortTermUnit = $this->manageStoreSecretKeys(
                    $output,
                    StorageTerm::veryShort(),
                    $currentVeryShortTermUnit,
                    $createMissingPastKeys,
                );
                $currentShortTermUnit = $this->manageStoreSecretKeys(
                    $output,
                    StorageTerm::short(),
                    $currentShortTermUnit,
                    $createMissingPastKeys,
                );
                $currentLongTermUnit = $this->manageStoreSecretKeys(
                    $output,
                    StorageTerm::long(),
                    $currentLongTermUnit,
                    $createMissingPastKeys,
                );

                if (!$this->invoke('Cache keys in memory', $output, fn () => $this->securityService->cacheKeys(false))) {
                    return self::FAILURE;
                }

                if ($singleRun) {
                    return self::SUCCESS;
                }

                $createMissingPastKeys = false;

                sleep(60);
            } catch (Throwable) {
                // fatal error, exit so the auto-restart kicks in
                return self::FAILURE;
            }
        }
    }
}
