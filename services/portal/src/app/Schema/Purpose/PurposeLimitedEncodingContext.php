<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

use MinVWS\Codable\EncodingContext;

class PurposeLimitedEncodingContext extends EncodingContext implements PurposeLimited
{
    public function __construct(private readonly PurposeLimitation $purposeLimitation, ?self $parent = null)
    {
        parent::__construct($parent);
    }

    public function getPurposeLimitation(): PurposeLimitation
    {
        return $this->purposeLimitation;
    }

    public function createChildContext(): self
    {
        return new self($this->getPurposeLimitation(), $this);
    }
}
