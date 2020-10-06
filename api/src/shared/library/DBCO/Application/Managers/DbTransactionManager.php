<?php
namespace DBCO\Application\Managers;

use PDO;

class DbTransactionManager implements TransactionManager
{
    /**
     * @var PDO The database connection
     */
    private PDO $connection;

    /**
     * Constructor.
     *
     * @param PDO $connection The database connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

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
     * @throws \Exception
     */
    function run(Callable $callback)
    {
        try {
            $this->connection->beginTransaction();
            $result = $callback();
            $this->connection->commit();
            return $result;
        } catch (\Exception $ex) {
            $this->connection->rollBack();
            throw $ex;
        }
    }
}
