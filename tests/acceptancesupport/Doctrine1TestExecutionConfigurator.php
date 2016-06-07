<?php
namespace acceptancesupport\PSB\Persistence\Doctrine1;


use acceptancesupport\PSB\Core\Scenario\EndpointTestExecutionConfiguratorInterface;
use commonsupport\PSB\Persistence\Doctrine1\SchemaHelper;
use commonsupport\PSB\Persistence\Doctrine1\StorageConfigCollection;
use PSB\Core\EndpointConfigurator;
use PSB\Persistence\Doctrine1\Doctrine1PersistenceConfigurator;
use PSB\Persistence\Doctrine1\Doctrine1PersistenceDefinition;
use PSB\Persistence\Doctrine1\LogicalConnection;

class Doctrine1TestExecutionConfigurator implements EndpointTestExecutionConfiguratorInterface
{
    /**
     * @var array
     */
    private $connectionDSN;

    /**
     * @var array
     */
    private $storageConfigs;

    /**
     * @param string $connectionDSN
     * @param string $storageConfigs
     */
    public function __construct($connectionDSN, $storageConfigs)
    {
        $this->connectionDSN = $connectionDSN;
        $this->storageConfigs = json_decode($storageConfigs, true);
    }

    /**
     * @param EndpointConfigurator $endpointConfigurator
     */
    public function configure(EndpointConfigurator $endpointConfigurator)
    {
        /** @var Doctrine1PersistenceConfigurator $persistenceConfigurator */
        $persistenceConfigurator = $endpointConfigurator->usePersistence(new Doctrine1PersistenceDefinition());
        $persistenceConfigurator->useDSN($this->connectionDSN);
    }

    public function cleanup()
    {
        $doctrineManager = \Doctrine_Manager::getInstance();
        $logicalConnection = new LogicalConnection(null, $this->connectionDSN, \Doctrine_Manager::getInstance());
        $configCollection = StorageConfigCollection::fromArray($this->storageConfigs);
        $schemaHelper = new SchemaHelper($logicalConnection, $configCollection->asArray());
        $schemaHelper->dropAll();
        $doctrineManager->reset();
    }
}
