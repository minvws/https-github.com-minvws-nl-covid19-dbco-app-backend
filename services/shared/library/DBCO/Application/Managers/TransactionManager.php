<?php
namespace DBCO\Shared\Application\Managers;

use Exception;

interface TransactionManager
{
    /**
     * Runs the given code block inside a transaction.
     *
     * When everything is successful the changes are committed.
     * When an exception occurs everything is rolled back.
     *
     * The response of the callback is returned by this method.
     * If an exception occurs, it is rethrown after rolling back.
     *
     * @param callable $callback Callback.
     *
     * @return mixed Callback result
     *
     * @throws Exception
     */
    function run(Callable $callback);
}
