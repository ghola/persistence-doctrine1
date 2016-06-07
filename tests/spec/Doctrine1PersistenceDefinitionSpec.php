<?php

namespace spec\PSB\Persistence\Doctrine1;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Persistence\Doctrine1\Doctrine1PersistenceConfigurator;
use PSB\Persistence\Doctrine1\Doctrine1PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

/**
 * @mixin Doctrine1PersistenceDefinition
 */
class Doctrine1PersistenceDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Persistence\Doctrine1\Doctrine1PersistenceDefinition');
    }

    function it_creates_a_persistence_configurator(Settings $settings)
    {
        $this->createConfigurator($settings)->shouldBeLike(
            new Doctrine1PersistenceConfigurator($settings->getWrappedObject())
        );
    }

    function it_formalizes_by_declaring_support_for_outbox()
    {
        $this->formalize();

        $this->hasSupportFor(StorageType::OUTBOX())->shouldReturn(true);
    }
}
