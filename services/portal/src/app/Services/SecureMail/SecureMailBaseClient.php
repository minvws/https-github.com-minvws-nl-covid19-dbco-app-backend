<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use MinVWS\DBCO\Enum\Models\MessageStatus;
use Webmozart\Assert\Assert;

use function array_map;
use function sprintf;

class SecureMailBaseClient implements SecureMailClient
{
    protected PendingRequest $http;

    /**
     * @throws SecureMailException
     */
    public function postMessage(SecureMailMessage $message): string
    {
        try {
            $response = $this->http
                ->post('messages', $message->toArray())
                ->throw();
        } catch (RequestException $requestException) {
            throw SecureMailException::fromThrowable($requestException);
        }

        $id = $response->json('id');
        Assert::string($id);

        return $id;
    }

    /**
     * @throws SecureMailException
     */
    public function getSecureMailStatusUpdates(DateTimeInterface $since): SecureMailStatusUpdateCollection
    {
        try {
            $response = $this->http
                ->get('messages/statusupdates', ['since' => $since->format('c')])
                ->throw();
        } catch (RequestException $requestException) {
            throw SecureMailException::fromThrowable($requestException);
        }

        $body = $response->json();
        Assert::isArray($body);
        Assert::keyExists($body, 'count');
        Assert::keyExists($body, 'total');

        $secureMailStatusUpdates = array_map(static function (array $message) {
            $messageStatus = MessageStatus::from($message['status']);

            return new SecureMailStatusUpdate(
                $message['id'],
                $message['notificationSentAt'] ? new CarbonImmutable($message['notificationSentAt']) : null,
                $messageStatus,
            );
        }, $body['messages']);

        return new SecureMailStatusUpdateCollection($body['count'], $body['total'], $secureMailStatusUpdates);
    }

    /**
     * @throws SecureMailException
     */
    public function delete(string $uuid): void
    {
        try {
            $this->http
                ->delete(sprintf('messages/%s', $uuid))
                ->throw();
        } catch (RequestException $requestException) {
            throw SecureMailException::fromThrowable($requestException);
        }
    }
}
