<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Dto\Osiris\Client\PutMessageResult;
use App\Dto\Osiris\Client\SoapMessage;
use App\Exceptions\Osiris\Client\ClientException;
use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Http\Client\Soap\Exceptions\NotAvailableException;
use App\Http\Client\Soap\Exceptions\SoapClientException;
use App\Http\Client\Soap\SoapClient;
use App\ValueObjects\OsirisNumber;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use SoapFault;
use Throwable;

use function array_map;
use function explode;
use function property_exists;
use function simplexml_load_string;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use function ucfirst;

final class OsirisSoapClient implements OsirisClient
{
    private const OSIRIS_EXPLANATION_ERROR_NEWLINE = '/.br';
    private const OSIRIS_EXPLANATION_WARNING_NEWLINE = '/.br-->';

    public function __construct(
        private readonly SoapClient $soapClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ErrorResponseException
     * @throws ClientException
     */
    public function putMessage(SoapMessage $soapMessage): PutMessageResult
    {
        $this->logger->debug(sprintf('Osiris XML message: %s', $soapMessage->getBodyAsXml()));

        try {
            $response = $this->soapClient->call('PutMessage', [$soapMessage->toArray()]);
        } catch (SoapClientException | SoapFault $exception) {
            $this->logMessageForFailedRequest($exception, $soapMessage->getBodyAsXml());
            throw ClientException::fromThrowable($exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * @throws ClientException
     * @throws ErrorResponseException
     */
    private function parseResponse(object $response): PutMessageResult
    {
        if (!property_exists($response, 'PutMessageResult')) {
            throw new ClientException('Missing PutMessageResult element');
        }

        $xml = simplexml_load_string($response->PutMessageResult);

        if (
            $xml instanceof SimpleXMLElement
            && (string) $xml->resultaat->bericht_ok === 'true'
            && (string) $xml->resultaat->melding_ok === 'true'
            && isset($xml->resultaat->osiris_nummer)
        ) {
            return new PutMessageResult(
                new OsirisNumber((int) $xml->resultaat->osiris_nummer),
                $this->parseExplanation((string) $xml->resultaat->fout_toelichting, self::OSIRIS_EXPLANATION_WARNING_NEWLINE),
            );
        }

        if (empty($xml->resultaat->fout_reden)) {
            throw new ErrorResponseException('Unknown', []);
        }

        $reason = (string) $xml->resultaat->fout_reden;
        $explanations = $this->parseExplanation((string) $xml->resultaat->fout_toelichting, self::OSIRIS_EXPLANATION_ERROR_NEWLINE);

        throw new ErrorResponseException($reason, $explanations);
    }

    private function parseExplanation(string $explanation, string $separator): array
    {
        $explanation = trim($explanation);

        if (str_starts_with($explanation, $separator)) {
            $explanation = substr($explanation, strlen($separator));
        }

        if (empty($explanation) || empty($separator)) {
            return [];
        }

        $explanations = explode($separator, $explanation);

        return array_map(static fn($e) => ucfirst(trim($e)), $explanations);
    }

    private function logMessageForFailedRequest(Throwable $exception, string $message): void
    {
        $context = ['error' => $exception->getMessage()];

        if (!$exception instanceof NotAvailableException) {
            $context['message'] = $message;
            $context['response'] = $this->soapClient->getLastResponse();
        }

        $this->logger->error('Request to Osiris failed', $context);
    }
}
