<?php
namespace specsupport\PSB\Persistence\Doctrine1\PhpSpec\Extension;


use commonsupport\PSB\Persistence\Doctrine1\SchemaHelper;
use commonsupport\PSB\Persistence\Doctrine1\StorageConfigCollection;
use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\ServiceContainer;
use PSB\Persistence\Doctrine1\LogicalConnection;
use specsupport\PSB\Persistence\Doctrine1\PhpSpec\Listener\DoctrineFixtureListener;
use specsupport\PSB\Persistence\Doctrine1\PhpSpec\Runner\Maintainer\DoctrineFixtureMaintainer;

class DoctrineFixtureExtension implements ExtensionInterface
{
    /**
     * @param ServiceContainer $container
     *
     * @throws \Exception
     */
    public function load(ServiceContainer $container)
    {
        $container->setShared(
            'doctrine_fixture.options',
            function (ServiceContainer $container) {
                $options = $container->getParam('doctrine_fixture_options');
                if (!$options) {
                    throw new \Exception('Cannot run tests without fixture options.');
                }

                if (!$options['connection_dsn']) {
                    throw new \Exception('Cannot run tests without database connection DSN.');
                }

                if (!$options['storage_configs']) {
                    throw new \Exception(
                        'Cannot run tests without knowing which table and schema to use for each storage type.'
                    );
                }

                return $options;
            }
        );

        $container->setShared(
            'doctrine_fixture.connection',
            function (ServiceContainer $container) {
                $options = $container->get('doctrine_fixture.options');
                return new LogicalConnection(null, $options['connection_dsn'], \Doctrine_Manager::getInstance());
            }
        );

        $container->setShared(
            'doctrine_fixture.schema_helper',
            function (ServiceContainer $container) {
                $options = (array)$container->get('doctrine_fixture.options');
                $configCollection = StorageConfigCollection::fromArray($options['storage_configs']);
                return new SchemaHelper(
                    $container->get('doctrine_fixture.connection'), $configCollection->asArray()
                );
            }
        );

        $container->setShared(
            'runner.maintainers.doctrine_fixture',
            function (ServiceContainer $container) {
                return new DoctrineFixtureMaintainer(
                    $container->get('doctrine_fixture.connection'),
                    $container->get('doctrine_fixture.schema_helper')
                );
            }
        );

        $container->setShared(
            'event_dispatcher.listeners.doctrine_fixture',
            function (ServiceContainer $container) {
                return new DoctrineFixtureListener($container->get('doctrine_fixture.schema_helper'));
            }
        );
    }
}
