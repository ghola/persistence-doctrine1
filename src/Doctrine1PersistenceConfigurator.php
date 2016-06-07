<?php
namespace PSB\Persistence\Doctrine1;


use PSB\Core\Persistence\PersistenceConfigurator;

class Doctrine1PersistenceConfigurator extends PersistenceConfigurator
{
    /**
     * A DSN is essential when the bus attempts to automatically recover from disconnects.
     * The DNS formats are as described in the Doctrine documentation.
     *
     * @see http://doctrine.readthedocs.org/en/latest/en/manual/introduction-to-connections.html
     *
     * @param string $dsn
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function useDSN($dsn)
    {
        $this->settings->set(Doctrine1KnownSettingsEnum::DSN, $dsn);
        return $this;
    }

    /**
     * You can also specify a Doctrine_Manager instance.
     * If not specified, one will be obtained using Doctrine_Manager::getInstance()
     *
     * @param \Doctrine_Manager $manager
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function useManager(\Doctrine_Manager $manager)
    {
        $this->settings->set(Doctrine1KnownSettingsEnum::MANAGER, $manager);
        return $this;
    }

    /**
     * If you're using multiple connections in your application, it's imperative that you specify
     * which connection name should be used. Not doing so can render your application useless when
     * the bus attempts to automatically recover from disconnects.
     *
     * @param string $name
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function useConnectionName($name)
    {
        $this->settings->set(Doctrine1KnownSettingsEnum::CONNECTION_NAME, $name);
        return $this;
    }

    /**
     * Instead of passing the connection name, dsn and manager one by one, you can do it in a single step
     * by passing a LogicalConnection which is a wrapper to the 3 mentioned before.
     *
     * @param LogicalConnection $connection
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function useLogicalConnection(LogicalConnection $connection)
    {
        $this->settings->set(Doctrine1KnownSettingsEnum::LOGICAL_CONNECTION, $connection);
        return $this;
    }

    /**
     * @param string $tableName
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function useOutboxMessagesTableName($tableName)
    {
        $this->settings->set(Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME, $tableName);
        return $this;
    }

    /**
     * @param string $tableName
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function useOutboxEndpointsTableName($tableName)
    {
        $this->settings->set(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME, $tableName);
        return $this;
    }
}
