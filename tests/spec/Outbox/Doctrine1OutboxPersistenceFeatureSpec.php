<?php

namespace spec\PSB\Persistence\Doctrine1\Outbox;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Feature\FeatureStateEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Outbox\OutboxFeature;
use PSB\Core\Outbox\OutboxStorageInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;
use PSB\Persistence\Doctrine1\LogicalConnection;
use PSB\Persistence\Doctrine1\LogicalConnectionFactory;
use PSB\Persistence\Doctrine1\Doctrine1KnownSettingsEnum;
use PSB\Persistence\Doctrine1\Outbox\Doctrine1OutboxPersistenceFeature;
use PSB\Persistence\Doctrine1\Outbox\OutboxPersister;

/**
 * @mixin Doctrine1OutboxPersistenceFeature
 */
class Doctrine1OutboxPersistenceFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Persistence\Doctrine1\Outbox\Doctrine1OutboxPersistenceFeature');
    }

    function it_describes_as_depending_on_outbox_feature_and_by_registering_defaults(Settings $settings)
    {
        $this->describe();
        $this->configureDefaults($settings);

        $this->getDependencies()->shouldReturn([[OutboxFeature::class]]);
        $settings->setDefault(
            Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME,
            'psb_outbox_messages'
        )->shouldBeCalled();
        $settings->setDefault(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME, 'psb_outbox_endpoints')
            ->shouldBeCalled();

    }

    function it_registers_services_in_the_container_during_setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->tryGet(OutboxFeature::class)->willReturn(FeatureStateEnum::ACTIVE);

        $builder->defineSingleton(
            LogicalConnection::class,
            new LogicalConnectionFactory($settings->getWrappedObject())
        )->shouldBeCalled();

        $builder->defineSingleton(OutboxPersister::class, Argument::type('\Closure'))->shouldBeCalled();
        $builder->defineSingleton(OutboxStorageInterface::class, Argument::type('\Closure'))->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
