<?php
namespace PSB\Persistence\Doctrine1;


use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Util\Guard;

class LogicalConnection
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @var \Doctrine_Manager
     */
    private $manager;

    /**
     * @param string            $name
     * @param string            $dsn
     * @param \Doctrine_Manager $manager
     */
    public function __construct($name = null, $dsn, \Doctrine_Manager $manager)
    {
        Guard::againstNullAndEmpty('dns', $dsn);

        $this->name = $name === '' ? null : $name;
        $this->dsn = $dsn;
        $this->manager = $manager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @return \Doctrine_Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return int
     */
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rolls back the transaction.
     * It makes sure that the connection is in the correct state regardless of what happened before.
     * Correct state means that the connection does not have a transaction nesting level > 0
     *
     * @return bool
     */
    public function rollBack()
    {
        /**
         * Roll back all the way as this is supposed to be the top level transaction and we want to reset
         * the nesting level
         */
        $transactionNestingLevel = $this->getConnection()->getTransactionLevel();
        for ($i = 0; $i < $transactionNestingLevel - 1; $i++) {
            $this->getConnection()->rollBack();
        }
        return $this->getConnection()->rollBack();
    }

    /**
     * @param \Closure $func
     *
     * @throws \Exception
     */
    public function transactional(\Closure $func)
    {
        $this->beginTransaction();
        try {
            $func($this);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return int
     */
    public function getTransactionNestingLevel()
    {
        return $this->getConnection()->getTransactionLevel();
    }

    /**
     * @param string $query
     * @param array  $params
     *
     * @return \Doctrine_Adapter_Statement|\PDOStatement
     */
    public function executeUpdate($query, array $params = [])
    {
        return $this->getConnection()->standaloneQuery($query, $params);
    }

    /**
     * @param string $query
     * @param array  $params
     *
     * @return \PDOStatement|\Doctrine_Adapter_Statement
     */
    public function executeQuery($query, array $params = [])
    {
        return $this->getConnection()->standaloneQuery($query, $params);
    }

    /**
     * @param string $tableName
     * @param array  $data
     *
     * @return int
     */
    public function insert($tableName, array $data)
    {
        $columns = array_keys($data);
        foreach ($columns as $key => $value) {
            $columns[$key] = $this->getConnection()->quoteIdentifier($value);
        }

        $query = 'INSERT INTO ' . $this->getConnection()->quoteIdentifier($tableName)
            . ' (' . implode(', ', $columns) . ')'
            . ' VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')';

        return $this->getConnection()->exec($query, array_values($data));
    }

    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * @param \Exception $e
     *
     * @return \Exception
     */
    public function reconnectIfNeeded(\Exception $e)
    {
        // presumably, any exception caught here is related to some connection error
        if (!$this->ping()) {
            // if pinging fails, we try to reconnect
            try {
                $this->manager->closeConnection($this->getConnection());
                $this->getConnection()->connect();
            } catch (\Exception $e) {
                // if reconnecting fails, there is no way that the bus can continue to function
                return new CriticalErrorException("Database connection failed.", 0, $e);
            }
        }

        return $e;
    }

    /**
     * @return bool
     * @throws \Doctrine_Connection_Exception
     */
    public function connect()
    {
        return $this->getConnection()->connect();
    }

    /**
     * @return \Doctrine_Connection
     */
    public function getWrappedConnection()
    {
        return $this->getConnection();
    }

    /**
     * @return bool
     */
    public function ping()
    {
        $this->connect();

        try {
            $this->executeQuery($this->getDummySelectSQL());
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isEmbeddedDatabase()
    {
        $driverName = $this->getConnection()->getDriverName();
        if (stripos($driverName, 'sqlite') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param string $tableName
     * @param array  $fields
     * @param array  $options
     */
    public function createTable($tableName, array $fields = [], array $options = [])
    {
        $this->getConnection()->export->createTable($tableName, $fields, $options);
    }

    /**
     * @return \Doctrine_Connection
     * @throws \Doctrine_Connection_Exception
     * @throws \Doctrine_Manager_Exception
     */
    private function getConnection()
    {
        // if there is a name and a connection with that name exists, use that one
        if ($this->name && $this->manager->contains($this->name)) {
            return $this->manager->getConnection($this->name);
        }

        // if there is a name and no connection with that name exists, create one
        if ($this->name) {
            return $this->manager->openConnection($this->dsn, $this->name);
        }

        // if there is no name (so it's not using multiple connections) and one connection is present, use that one
        if (!$this->name && $this->manager->count()) {
            return $this->manager->getCurrentConnection();
        }

        // if there is no name (so it's not using multiple connections) and no connection is present, create one
        return $this->manager->openConnection($this->dsn);
    }

    /**
     * Taken from doctrine 2
     *
     * @return string
     */
    private function getDummySelectSQL()
    {
        $driverName = strtolower($this->getConnection()->getDriverName());
        switch ($driverName) {
            case 'db2':
                $sql = 'SELECT 1 FROM sysibm.sysdummy1';
                break;
            case 'oci':
                $sql = 'SELECT 1 FROM dual';
                break;
            default:
                $sql = 'SELECT 1';
        }
        return $sql;
    }
}
