<?php

declare(strict_types=1);

namespace Tests\Traits;

use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use PHPUnit\Framework\Attributes\Before;
use Tests\Mocks\MockEncryptionHelper;

trait MocksEncryptionHelper
{
    #[Before]
    protected function setUpEncryptionHelper(): void
    {
        $this->afterApplicationCreated(function (): void {
            // mock encryption helper so we don't need the hsm
            // we let the mock json_encode and base64_encode the data to make it a bit more realistic and to make
            // sure the data really is transformed before storing and after loading
            $this->app->bind(EncryptionHelper::class, MockEncryptionHelper::class);
        });
    }
}
