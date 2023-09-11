<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment\TokenEncoder;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Token;
use App\Services\Assignment\TokenEncoder\FirebaseHs256TokenEncoder;
use App\Services\Assignment\TokenEncoder\TokenEncoder;
use App\Services\Assignment\TokenResource;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Feature\FeatureTestCase;

#[Group('assignment')]
final class FirebaseHs256TokenEncoderTest extends FeatureTestCase
{
    use MatchesSnapshots;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        // To make the output of the service consistent regardless of whatever the value is set to for test env:
        $config->set('assignment.jwt.secret', 'MY_JWT_SECRET');
    }

    public function testItCanBeInitialized(): void
    {
        $encoder = $this->app->make(FirebaseHs256TokenEncoder::class);

        $this->assertInstanceOf(FirebaseHs256TokenEncoder::class, $encoder);
        $this->assertInstanceOf(TokenEncoder::class, $encoder);
    }

    public function testEncode(): void
    {
        /** @var FirebaseHs256TokenEncoder $encoder */
        $encoder = $this->app->make(FirebaseHs256TokenEncoder::class);

        $payload = new Token(
            iss: '',
            aud: '',
            sub: '',
            exp: 0,
            iat: 0,
            jti: null,
            res: Collection::make([
                new TokenResource(mod: AssignmentModelEnum::cases()[0], ids: []),
            ]),
        );

        $this->assertMatchesSnapshot($encoder->encode($payload));
    }
}
