<?php

namespace spec\PSB\Persistence\Doctrine1;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Util\Settings;
use PSB\Persistence\Doctrine1\Doctrine1KnownSettingsEnum;
use PSB\Persistence\Doctrine1\LogicalConnection;
use PSB\Persistence\Doctrine1\LogicalConnectionFactory;

/**
 * @mixin LogicalConnectionFactory
 */
class LogicalConnectionFactorySpec extends ObjectBehavior
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
        $this->shouldHaveType('PSB\Persistence\Doctrine1\LogicalConnectionFactory');
    }

    function it_returns_the_logical_connection_from_settings_if_it_is_set(LogicalConnection $connection)
    {
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::LOGICAL_CONNECTION)->willReturn($connection);

        $this->__invoke()->shouldReturn($connection);
    }

    function it_throws_if_no_logical_connection_and_no_dsn_are_found_in_settings()
    {
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::LOGICAL_CONNECTION)->willReturn(null);
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::DSN)->willReturn(null);

        $this->shouldThrow('PSB\Core\Exception\UnexpectedValueException')->during('__invoke');
    }

    function it_creates_by_using_the_doctrine_manager_and_connection_name_from_settings_if_they_are_set(
        \Doctrine_Manager $manager
    ) {
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::LOGICAL_CONNECTION)->willReturn(null);
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::DSN)->willReturn('somedsn');
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::CONNECTION_NAME)->willReturn('somename');
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::MANAGER)->willReturn($manager);

        $this->__invoke()->shouldBeLike(new LogicalConnection('somename', 'somedsn', $manager->getWrappedObject()));
    }

    function it_creates_by_using_a_new_doctrine_manager_if_one_not_found_in_settings()
    {
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::LOGICAL_CONNECTION)->willReturn(null);
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::DSN)->willReturn('somedsn');
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::CONNECTION_NAME)->willReturn(null);
        $this->settingsMock->tryGet(Doctrine1KnownSettingsEnum::MANAGER)->willReturn(null);

        $this->__invoke()->shouldBeLike(new LogicalConnection(null, 'somedsn', \Doctrine_Manager::getInstance()));
    }
}
