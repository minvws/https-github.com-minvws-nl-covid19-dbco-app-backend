<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Actions;

use Slim\Psr7\Request;

/**
 * Validation exception.
 *
 * @package DBCO\Shared\Application\Actions
 */
class ValidationException extends ActionException
{
    /**
     * @var ValidationError[]
     */
    private array $errors;

    /**
     * Constructs a new error.
     *
     * @param Request           $request Request.
     * @param string            $type    Error type.
     * @param string            $message Error message.
     * @param ValidationError[] $errors  Validation errors.
     * @param int               $code    Status code.
     */
    public function __construct(Request $request, array $errors, string $type = 'validationError', string $message = 'The server cannot or will not process the request due to an apparent client error.', int $code = self::BAD_REQUEST)
    {
        parent::__construct($request, $type, $message, $code);
        $this->errors = $errors;
    }

    /**
     * Prepare for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['errors'] = array_map(fn ($e) => $e->jsonSerialize(), $this->errors);
        return $data;
    }
}
