<?php
declare(strict_types=1);

namespace App\Application\Helpers;

interface RandomKeyGeneratorInterface
{
    public function generateToken(int $length = 6): string;
    public function generateKey(int $length = 32): string;
}
