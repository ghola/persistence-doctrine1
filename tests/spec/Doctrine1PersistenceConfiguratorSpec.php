<?php

namespace spec\PSB\Persistence\Doctrine1;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Util\Settings;
use PSB\Persistence\Doctrine1\Doctrine1KnownSettingsEnum;
use PSB\Persistence\Doctrine1\Doctrine1PersistenceConfigurator;

/**
 * @mixin Doctrine1PersistenceConfigurator
 */
class Doctrine1PersistenceConfiguratorSpec extends ObjectBehavior
{
    /**
     * @var Settings
     */
    protected $settingsMock;

    function let(Settings $settings)
    {
        $this->settingsMock = $settings;
        $this->beConstructedWith($settings);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Persistence\Doctrine1\Doctrine1PersistenceConfigurator');
    }

    function it_uses_dsn()
    {
        $this->settingsMock->set(
            Doctrine1KnownSettingsEnum::DSN,
            'somedsn'
        )->shouldBeCalled();

        $this->useDSN('somedsn')->shouldReturn($this);
    }

    function it_uses_doctrine_manager(\Doctrine_Manager $manager)
    {
        $this->settingsMock->set(
            Doctrine1KnownSettingsEnum::MANAGER,
            $manager
        )->shouldBeCalled();

        $this->useManager($manager)->shouldReturn($this);
    }

    function it_uses_connection_name($name)
    {
        $this->settingsMock->set(
            Doctrine1KnownSettingsEnum::CONNECTION_NAME,
            $name
        )->shouldBeCalled();

        $this->useConnectionName($name)->shouldReturn($this);
    }

    function it_uses_outbox_messages_table_name($tableName)
    {
        $this->settingsMock->set(
            Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME,
            $tableName
        )->shouldBeCalled();

        $this->useOutboxMessagesTableName($tableName)->shouldReturn($this);
    }

    function it_uses_outbox_endpoints_table_name($tableName)
    {
        $this->settingsMock->set(
            Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME,
            $tableName
        )->shouldBeCalled();

        $this->useOutboxEndpointsTableName($tableName)->shouldReturn($this);
    }
}
