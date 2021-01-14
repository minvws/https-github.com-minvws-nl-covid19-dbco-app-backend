<?php
namespace DBCO\Shared\Application\Managers;

use Exception;
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
     * @throws Exception
     */
    function run(Callable $callback)
    {
        // We support one nested level of transactions using savepoints.
        // This is primarily used for running unit tests with database
        // access in which case the transaction is controlled by the unit
        // tests and transaction manager uses savepoints.
        $inTransaction = $this->connection->inTransaction();

        try {
            if ($inTransaction) {
                $this->connection->exec('SAVEPOINT SP');
            } else {
                $this->connection->beginTransaction();
            }

            $result = $callback();

            if ($inTransaction) {
                $this->connection->exec('RELEASE SAVEPOINT SP');
            } else {
                $this->connection->commit();
            }

            return $result;
        } catch (Exception $ex) {
            if ($inTransaction) {
                $this->connection->exec('ROLLBACK TO SAVEPOINT SP');
            } else {
                $this->connection->rollBack();
            }

            throw $ex;
        }
    }
}
