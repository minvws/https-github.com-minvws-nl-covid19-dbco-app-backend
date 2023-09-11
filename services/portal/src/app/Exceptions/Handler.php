<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\Assignment\Exception\AssignmentInternalValidationException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

use function response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(static function (NotFoundHttpException $e, Request $request) {
            if ($request->isXmlHttpRequest()) {
                return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
        });

        /** @var Config $config */
        $config = $this->container->make(Config::class);

        $this->map(static fn (AssignmentInternalValidationException $e): Throwable
            => $config->get('app.debug') ? $e->getPrevious() : $e);
    }
}
