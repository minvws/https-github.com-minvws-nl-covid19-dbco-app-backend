<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentInternalValidationException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function assert;

#[Group('assignment')]
class AssignmentInternalValidationExceptionTest extends FeatureTestCase
{
    use MockeryPHPUnitIntegration;

    public function testItRendersTheExceptionAsJsonWithAllAvailableErrorMessages(): void
    {
        // Arrange / Given
        /** @var Config $config */
        $config = $this->app->make(Config::class);
        $config->set('app.debug', true);

        $validationException = $this->getValidationExceptionFixture();

        $e = new AssignmentInternalValidationException(
            message: $this->faker->words(5, asText: true),
            validationException: $validationException,
        );

        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->app->make(ExceptionHandler::class);

        $request = new Request();
        $request->headers->set('Accept', 'application/json');

        // Act / When
        $response = $exceptionHandler->render($request, $e);

        // Assert / Then
        $this->assertInstanceOf(JsonResponse::class, $response);
        assert($response instanceof JsonResponse);
        $this->assertSame(
            [
                'message' => 'The attribute one field is required. (and 2 more errors)',
                'errors' => [
                    'attribute_one' => [
                        'The attribute one field is required.',
                        'The attribute one must be an array.',
                    ],
                    'attribute_two' => [
                        'The attribute two field is required.',
                    ],
                ],
            ],
            $response->getData(assoc: true),
        );
        $this->assertSame($validationException->status, $response->getStatusCode());
    }

    public function testItRendersTheExceptionAsJsonResponseWithMinimalInformationWhenDebugModeIsDisabled(): void
    {
        // Arrange / Given
        /** @var Config $config */
        $config = $this->app->make(Config::class);
        $config->set('app.debug', false);

        /** @var ValidationException&MockInterface $validationException */
        $validationException = Mockery::mock(ValidationException::class);

        $e = new AssignmentInternalValidationException(
            message: $message = 'My message',
            validationException: $validationException,
            status: $httpCode = 500,
        );

        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->app->make(ExceptionHandler::class);

        $request = new Request();
        $request->headers->set('Accept', 'application/json');

        // Act / When
        $response = $exceptionHandler->render($request, $e);

        // Assert / Then
        $this->assertInstanceOf(JsonResponse::class, $response);
        assert($response instanceof JsonResponse);
        $this->assertSame(['message' => $message], $response->getData(assoc: true));
        $this->assertSame($httpCode, $response->getStatusCode());
    }

    private function getValidationExceptionFixture(): ValidationException
    {
        /** @var ValidatorFactory $validatorFactory */
        $validatorFactory = $this->app->make(ValidatorFactory::class);
        $validatorFactory->getTranslator()->setLocale('en');

        $validator = $validatorFactory->make(data: [], rules: []);
        $validator->addFailure(attribute: 'attribute_one', rule: 'required');
        $validator->addFailure(attribute: 'attribute_one', rule: 'array');
        $validator->addFailure(attribute: 'attribute_two', rule: 'required');

        return new ValidationException($validator);
    }
}
