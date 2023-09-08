<?php

declare(strict_types=1);

namespace App\Services\BcoNumber;

use Throwable;

use function collect;
use function implode;
use function mb_strlen;
use function random_int;

class BcoNumberGenerator
{
    private const TYPE_BCO_PORTAL = '1';

    public function __construct(
        private readonly string $allowedNumberChars,
        private readonly string $allowedAlphaChars,
    ) {
    }

    public function buildCode(): string
    {
        $parts = collect([
            $this->makePart(2, $this->allowedAlphaChars) . self::TYPE_BCO_PORTAL,
            $this->makePart(3, $this->allowedNumberChars),
            $this->makePart(3, $this->allowedNumberChars),
        ]);

        return $parts->join('-');
    }

    private function makePart(int $length, string $allowedChars): string
    {
        $chars = [];
        $max = mb_strlen($allowedChars, '8bit') - 1;

        if ($max < 0) {
            throw new BcoNumberException('Max part length cannot be less than 0');
        }

        for ($i = 0; $i < $length; $i++) {
            try {
                $chars [] = $allowedChars[random_int(0, $max)];
            } catch (Throwable) {
                throw new BcoNumberException('An appropriate source of randomness cannot be found.');
            }
        }

        return implode('', $chars);
    }
}
