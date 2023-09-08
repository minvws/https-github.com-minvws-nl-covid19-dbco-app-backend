<?php

declare(strict_types=1);

namespace DBCO\Shared\Application\Handlers;

/**
 * PHP error.
 *
 * @package DBCO\Shared\Application\Handlers
 */
class PHPError extends \Exception
{
    /**
     * Constructor.
     */
    public function __construct(array $error)
    {
        switch ($error['type']) {
            case E_USER_ERROR:
                $prefix = "FATAL ERROR";
                break;
            case E_USER_WARNING:
                $prefix = "WARNING";
                break;
            case E_USER_NOTICE:
                $prefix = "NOTICE";
                break;
            default:
                $prefix = "ERROR";
        }

        $message = "$prefix: {$error['message']} at {$error['file']}({$error['line']})";
        parent::__construct($message, $error['type']);
    }
}
