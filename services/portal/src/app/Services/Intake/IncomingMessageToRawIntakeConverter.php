<?php

declare(strict_types=1);

namespace App\Services\Intake;

use App\Exceptions\IntakeEncryptionException;
use App\Exceptions\IntakeException;
use App\Models\Intake\RawIntake;
use App\Services\MessageQueue\IncomingMessage;
use Exception;
use JsonException;
use MinVWS\Codable\Decoder;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use Psr\Log\LoggerInterface;
use SodiumException;
use Throwable;
use Webmozart\Assert\Assert;

use function base64_decode;
use function config;
use function is_string;
use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class IncomingMessageToRawIntakeConverter
{
    public function __construct(
        private EncryptionHelper $encryptionHelper,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws IntakeException
     */
    public function convert(IncomingMessage $incomingMessage): RawIntake
    {
        try {
            $data = $incomingMessage->getData();

            $decoder = new Decoder();
            $container = $decoder->decode($data);

            $this->logger->info(sprintf('Decrypting identity data for message "%s"...', $incomingMessage->getId()));

            $identityDataPublicKey = config('misc.intake.identity_data_public_key', '');
            Assert::string($identityDataPublicKey);

            $identityDataPublicKey = base64_decode($identityDataPublicKey, true);
            if (!is_string($identityDataPublicKey)) {
                throw new Exception('identity_data_public_key can not be decoded');
            }
            $identityData = $this->decryptEncryptedData($container->identityData->decodeString(), $identityDataPublicKey);

            $this->logger->info(sprintf('Decrypting intake data for message "%s"...', $incomingMessage->getId()));
            $intakeData = $this->decryptEncryptedData($container->intakeData->decodeString());

            $this->logger->info(sprintf('Decrypting handover data for message "%s"...', $incomingMessage->getId()));
            $encryptedHandoverData = $container->handoverData->decodeStringIfPresent();
            $handoverData = $encryptedHandoverData !== null ? $this->decryptEncryptedData($encryptedHandoverData) : null;

            return new RawIntake(
                $incomingMessage->getId(),
                $container->type->decodeString(),
                $container->source->decodeString(),
                $identityData,
                $intakeData,
                $handoverData,
                $container->receivedAt->decodeDateTime(),
            );
        } catch (Throwable $e) {
            throw IntakeException::fromThrowable($e);
        }
    }

    /**
     * @throws IntakeException
     */
    private function decryptEncryptedData(string $data, ?string $remotePublicKey = null): array
    {
        try {
            $binaryData = base64_decode($data, true);
            if (!is_string($binaryData)) {
                throw new IntakeException('data can not be base64-decoded');
            }

            $unsealedData = $remotePublicKey === null
                ? $this->encryptionHelper->unsealDataWithKey($binaryData, SecurityModule::SK_PUBLIC_PORTAL)
                : $this->encryptionHelper->unsealDataWithDerivedKey($binaryData, SecurityModule::SK_PUBLIC_PORTAL, $remotePublicKey);

            return json_decode($unsealedData, true, 512, JSON_THROW_ON_ERROR);
        } catch (SodiumException $sodiumException) {
            throw IntakeEncryptionException::fromThrowable($sodiumException);
        } catch (JsonException $jsonException) {
            throw IntakeException::fromThrowable($jsonException);
        }
    }
}
