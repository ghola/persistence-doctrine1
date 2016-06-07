<?php
namespace PSB\Persistence\Doctrine1\Outbox;


use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Outbox\OutboxFeature;
use PSB\Core\Outbox\OutboxStorageInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;
use PSB\Persistence\Doctrine1\Doctrine1KnownSettingsEnum;
use PSB\Persistence\Doctrine1\LogicalConnection;
use PSB\Persistence\Doctrine1\LogicalConnectionFactory;
use PSB\Persistence\Doctrine1\Outbox\SchemaProvider\OutboxEndpointSchemaProvider;
use PSB\Persistence\Doctrine1\Outbox\SchemaProvider\OutboxMessageSchemaProvider;

class Doctrine1OutboxPersistenceFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->dependsOn(OutboxFeature::class);
        $this->registerDefault(
            function (Settings $settings) {
                $settings->setDefault(Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME, 'psb_outbox_messages');
                $settings->setDefault(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME, 'psb_outbox_endpoints');
            }
        );
    }

    /**
     * Method is called if all defined conditions are met and the feature is marked as enabled.
     * Use this method to configure and initialize all required components for the feature like
     * the steps in the pipeline or the instances/factories in the container.
     *
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
        $builder->defineSingleton(LogicalConnection::class, new LogicalConnectionFactory($settings));

        $builder->defineSingleton(
            OutboxPersister::class,
            function () use ($builder, $settings) {
                return new OutboxPersister(
                    $builder->build(LogicalConnection::class),
                    $settings->get(Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME),
                    $settings->get(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME)
                );
            }
        );

        $builder->defineSingleton(
            OutboxStorageInterface::class,
            function () use ($builder, $settings) {
                return new Doctrine1OutboxStorage(
                    $builder->build(OutboxPersister::class),
                    new OutboxMessageConverter(),
                    $settings->get(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINT_ID)
                );
            }
        );

        $this->registerStartupTask(
            function () use ($builder, $settings) {
                return new EndpointIdLoaderFeatureStartupTask(
                    $builder->build(OutboxPersister::class),
                    $settings,
                    $settings->get(KnownSettingsEnum::ENDPOINT_NAME)
                );
            }
        );

        $this->registerStartupTask(
            function () use ($builder, $settings) {
                return new OutboxCleanerFeatureStartupTask(
                    $builder->build(OutboxPersister::class),
                    new \DateTime('now', new \DateTimeZone('UTC')),
                    $settings->tryGet(KnownSettingsEnum::DAYS_TO_KEEP_DEDUPLICATION_DATA)
                );
            }
        );

        $this->registerInstallTask(
            function () use ($builder, $settings) {
                return new OutboxTablesCreatorFeatureInstallTask(
                    $builder->build(LogicalConnection::class),
                    new OutboxEndpointSchemaProvider(),
                    new OutboxMessageSchemaProvider(),
                    $settings->get(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME),
                    $settings->get(Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME)
                );
            }
        );
    }
}
