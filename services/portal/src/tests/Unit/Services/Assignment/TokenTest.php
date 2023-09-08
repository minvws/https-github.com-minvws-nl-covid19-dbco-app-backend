<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment;

use App\Services\Assignment\Token;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class TokenTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $token = new Token(iss: '', aud: '', sub: '', exp: 0, iat: 0, jti: null, res: new Collection());

        $this->assertInstanceOf(Token::class, $token);
        $this->assertInstanceOf(Arrayable::class, $token);
    }

    public function testToArray(): void
    {
        $data = $this->getTokenData();

        $token = new Token(...$data);

        $expected = [
            'iss' => $data['iss'],
            'aud' => $data['aud'],
            'sub' => $data['sub'],
            'exp' => $data['exp'],
            'iat' => $data['iat'],
            'res' => $data['res']->toArray(),
            'jti' => $data['jti'],
        ];

        $this->assertSame($expected, $token->toArray());
    }

    public function testToArrayDoesNotReturnKeysWithNullValues(): void
    {
        $data = $this->getTokenData();
        $data['jti'] = null;

        $token = new Token(...$data);

        $expected = [
            'iss' => $data['iss'],
            'aud' => $data['aud'],
            'sub' => $data['sub'],
            'exp' => $data['exp'],
            'iat' => $data['iat'],
            'res' => $data['res']->toArray(),
        ];

        $this->assertSame($expected, $token->toArray());
    }

    private function getTokenData(): array
    {
        return [
            'iss' => $this->faker->word(),
            'aud' => $this->faker->word(),
            'sub' => $this->faker->word(),
            'exp' => $this->faker->randomNumber(8),
            'iat' => $this->faker->randomNumber(8),
            'jti' => $this->faker->word(),
            'res' => new Collection(),
        ];
    }
}
