<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class OpenApiValidatorMiddlewareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PsrHttpFactory::class, PsrHttpFactory::class);
        $this->app->bind(ServerRequestFactoryInterface::class, Psr17Factory::class);
        $this->app->bind(StreamFactoryInterface::class, Psr17Factory::class);
        $this->app->bind(UploadedFileFactoryInterface::class, Psr17Factory::class);
        $this->app->bind(ResponseFactoryInterface::class, Psr17Factory::class);
    }
}
