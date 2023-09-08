<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment\TokenProcessor;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentInternalValidationException;
use App\Services\Assignment\Token;
use App\Services\Assignment\TokenProcessor\JwtTokenProcessor;
use App\Services\Assignment\TokenProcessor\TokenProcessor;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[Group('assignment')]
final class JwtTokenProcessorTest extends FeatureTestCase
{
    public function testItCanBeInitialized(): void
    {
        $tokenProcessor = $this->app->make(JwtTokenProcessor::class);

        $this->assertInstanceOf(JwtTokenProcessor::class, $tokenProcessor);
        $this->assertInstanceOf(TokenProcessor::class, $tokenProcessor);
    }

    public function testFromPayload(): void
    {
        /** @var JwtTokenProcessor $tokenProcessor */
        $tokenProcessor = $this->app->make(JwtTokenProcessor::class);

        $payload = (object) [
            'iss' => $this->faker->word(),
            'aud' => $this->faker->word(),
            'sub' => $this->faker->uuid(),
            'exp' => 1,
            'iat' => 1,
            'res' => (object) [
                (object) [
                    'mod' => AssignmentModelEnum::Case_->value,
                    'ids' => (object) ['a', 'b'],
                ],
            ],
        ];

        /** @var Token $result */
        $result = $tokenProcessor->fromPayload($payload);

        $this->assertInstanceOf(Token::class, $result);
        $this->assertInstanceOf(Arrayable::class, $result);
        $this->assertEquals($this->objectToArray($payload), $result->toArray());
    }

    public function testFromPayloadWithInvalidTokenClaims(): void
    {
        /** @var JwtTokenProcessor $tokenProcessor */
        $tokenProcessor = $this->app->make(JwtTokenProcessor::class);

        $payload = (object) [
            'iss' => $this->faker->word(),
            'aud' => $this->faker->word(),
            'sub' => $this->faker->uuid(),
            'exp' => 1,
            'iat' => 1,
            'res' => (object) [
                (object) [
                    'mod' => -100,
                    'ids' => (object) ['a', 'b'],
                ],
            ],
        ];

        /** @var ValidationException&MockInterface $validationException */
        $validationException = Mockery::mock(ValidationException::class);

        $this->expectExceptionObject(
            new AssignmentInternalValidationException('Token structure invalid!', $validationException),
        );

        $tokenProcessor->fromPayload($payload);
    }

    private function objectToArray(object $object): array
    {
        return json_decode(
            json: json_encode(
                value: $object,
                flags: JSON_THROW_ON_ERROR,
            ),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
