<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenProcessor;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentInternalValidationException;
use App\Services\Assignment\Token;
use App\Services\Assignment\TokenResource;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use JsonException;
use stdClass;

use function assert;
use function is_array;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @implements TokenProcessor<stdClass,Token>
 * @phpstan-type StatelessTokenProcessorToken array{
 *      iss: string,
 *      aud: string,
 *      sub: string,
 *      exp: int,
 *      iat: int,
 *      jti: ?string,
 *      res: Collection<int,StatelessTokenProcessorTokenResource>
 *  }
 * @phpstan-type StatelessTokenProcessorTokenResource array{mod:int,ids:array<int,string>}
 */
final class JwtTokenProcessor implements TokenProcessor
{
    public function __construct(private readonly ValidationFactory $validationFactory)
    {
    }

    public function fromPayload(object $payload): object
    {
        $payload = $this->objectToArray($payload);

        $this->validate($payload);

        /** @var array $items */
        $items = $payload['res'];

        $resources = Collection::make($items);
        $resources = $resources->map(static function (array $resource): TokenResource {
            return new TokenResource(mod: AssignmentModelEnum::from($resource['mod']), ids: $resource['ids']);
        });

        return new Token(
            iss: $payload['iss'],
            aud: $payload['aud'],
            sub: $payload['sub'],
            exp: $payload['exp'],
            iat: $payload['iat'],
            jti: $payload['jti'] ?? null,
            res: $resources,
        );
    }

    /**
     * @throws AssignmentInternalValidationException
     */
    private function validate(array $payload): void
    {
        try {
            $rules = [
                'iss' => ['required', 'string'],
                'aud' => ['required', 'string'],
                'sub' => ['required', 'string', 'uuid'],
                'exp' => ['required', 'integer'],
                'iat' => ['required', 'integer'],
                'jti' => ['sometimes', 'string'],
                'res' => ['required', 'array', 'min:1'],
                'res.*' => ['required', 'array', 'min:1'],
                'res.*.mod' => ['required', new Enum(AssignmentModelEnum::class)],
                'res.*.ids' => ['required', 'array', 'min:1'],
                'res.*.ids.*' => ['required'],
            ];

            $this->validationFactory
                ->make($payload, $rules)
                ->validate();
        } catch (ValidationException $validationException) {
            throw new AssignmentInternalValidationException('Token structure invalid!', $validationException);
        }
    }

    /**
     * @throws JsonException
     */
    private function objectToArray(object $object): array
    {
        $array = json_decode(
            json_encode($object, flags: JSON_THROW_ON_ERROR),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        assert(is_array($array));

        return $array;
    }
}
