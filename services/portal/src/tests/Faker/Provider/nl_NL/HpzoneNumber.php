<?php

declare(strict_types=1);

namespace Tests\Faker\Provider\nl_NL;

use Faker\Provider\Base;

use function strtoupper;

class HpzoneNumber extends Base
{
    public function hpzoneNumber(): string
    {
        return strtoupper($this->generator->bothify('###?#######'));
    }
}
