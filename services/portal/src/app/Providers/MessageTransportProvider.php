<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\Config;
use App\Services\Message\Transport\SmtpMailerMessageTransport;
use App\Services\Message\Transport\ZivverMessageTransport;
use App\Services\SecureMail\SecureMailClient;
use App\Services\SecureMail\SecureMailClientManager;
use App\Services\SecureMail\SecureMailV1Client;
use App\Services\SecureMail\SecureMailV2Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Webmozart\Assert\Assert;

class MessageTransportProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecureMailClient::class, static function (Application $app): SecureMailClient {
            /** @var SecureMailClientManager $secureMailClientManager */
            $secureMailClientManager = $app->get(SecureMailClientManager::class);

            $secureMailClient = $secureMailClientManager->driver(Config::stringOrNull('secure_mail.default'));
            Assert::isInstanceOf($secureMailClient, SecureMailClient::class);

            return $secureMailClient;
        });

        $this->app->when(SecureMailV1Client::class)
            ->needs('$baseUrl')
            ->giveConfig('secure_mail.v1.base_url');
        $this->app->when(SecureMailV1Client::class)
            ->needs('$jwtSecret')
            ->giveConfig('secure_mail.v1.jwt_secret');

        $this->app->when(SecureMailV2Client::class)
            ->needs('$baseUrl')
            ->giveConfig('secure_mail.v2.base_url');
        $this->app->when(SecureMailV2Client::class)
            ->needs('$apiToken')
            ->giveConfig('secure_mail.v2.api_token');

        $this->app->singleton(SmtpMailerMessageTransport::class, static function (): SmtpMailerMessageTransport {
            return new SmtpMailerMessageTransport(Config::string('messagetransport.smtp.mailer'));
        });

        $this->app->singleton(ZivverMessageTransport::class, static function (): ZivverMessageTransport {
            return new ZivverMessageTransport(Config::string('messagetransport.zivver.mailer'));
        });
    }
}
