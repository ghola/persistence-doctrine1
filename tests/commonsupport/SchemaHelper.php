<?php
namespace commonsupport\PSB\Persistence\Doctrine1;


use PSB\Persistence\Doctrine1\LogicalConnection;

class SchemaHelper implements SchemaHelperInterface
{
    /**
     * @var LogicalConnection
     */
    private $connection;

    /**
     * @var StorageConfig[]
     */
    private $storageConfigs;

    /**
     * @param LogicalConnection $connection
     * @param StorageConfig[]   $storageConfigs
     */
    public function __construct(LogicalConnection $connection, array $storageConfigs)
    {
        $this->connection = $connection;
        $this->storageConfigs = $storageConfigs;
    }

    /**
     * @param string $storageType
     *
     * @throws \Exception
     */
    public function create($storageType)
    {
        $this->assertConfigsExists($storageType);
        $this->createTables($this->storageConfigs[$storageType]);
    }

    public function createAll()
    {
        foreach ($this->storageConfigs as $storageConfigs) {
            $this->createTables($storageConfigs);
        }
    }

    /**
     * @param string $storageType
     *
     * @throws \Exception
     */
    public function drop($storageType)
    {
        $this->assertConfigsExists($storageType);
        $this->dropTables($this->storageConfigs[$storageType]);
    }

    public function dropAll()
    {
        foreach ($this->storageConfigs as $storageConfigs) {
            $this->dropTables($storageConfigs);
        }
    }

    /**
     * @param string $storageType
     */
    public function clean($storageType)
    {
        $this->assertConfigsExists($storageType);
        $this->cleanTables($this->storageConfigs[$storageType]);
    }

    public function cleanAll()
    {
        foreach ($this->storageConfigs as $storageConfigs) {
            $this->cleanTables($storageConfigs);
        }
    }

    /**
     * @param string $storageType
     * @param string $tableNameType
     *
     * @return string
     */
    public function getTableFor($storageType, $tableNameType)
    {
        $this->assertConfigsExists($storageType);
        return $this->storageConfigs[$storageType]->getTableConfigs()[$tableNameType]->getTableName();
    }

    /**
     * @param StorageConfig $tableConfig
     */
    private function createTables(StorageConfig $tableConfig)
    {
        foreach ($tableConfig->getTableConfigs() as $tableConfig) {
            $schema = $tableConfig->getSchema();
            $this->connection->getWrappedConnection()->export->createTable(
                $schema->getTableName(),
                $schema->getDefinition(),
                $schema->getOptions()
            );
        }
    }

    /**
     * @param StorageConfig $tableConfig
     */
    private function dropTables(StorageConfig $tableConfig)
    {
        foreach ($tableConfig->getTableConfigs() as $tableConfig) {
            try {
                $this->connection->getWrappedConnection()->export->dropTable($tableConfig->getSchema()->getTableName());
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param StorageConfig $tableConfig
     */
    private function cleanTables(StorageConfig $tableConfig)
    {
        foreach ($tableConfig->getTableConfigs() as $tableConfig) {
            $this->connection->executeQuery("DELETE FROM {$tableConfig->getTableName()}");
        }
    }

    /**
     * @param string $storageType
     *
     * @throws \Exception
     */
    private function assertConfigsExists($storageType)
    {
        if (!isset($this->storageConfigs[$storageType])) {
            throw new \Exception("No tables defined for storage type '$storageType'.");
        }
    }
}
