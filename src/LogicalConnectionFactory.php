<?php
namespace PSB\Persistence\Doctrine1;


use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\Util\Settings;

class LogicalConnectionFactory
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return LogicalConnection
     */
    public function __invoke()
    {
        $logicalConnection = $this->settings->tryGet(Doctrine1KnownSettingsEnum::LOGICAL_CONNECTION);
        if ($logicalConnection) {
            return $logicalConnection;
        }

        $dsn = $this->settings->tryGet(Doctrine1KnownSettingsEnum::DSN);
        if (!$dsn) {
            throw new UnexpectedValueException(
                "The Doctrine 1 persistence requires a DSN. You can provide it through the persistence configurator."
            );
        }

        $manager = $this->settings->tryGet(Doctrine1KnownSettingsEnum::MANAGER);
        if (!$manager) {
            $manager = \Doctrine_Manager::getInstance();
        }

        $connectionName = $this->settings->tryGet(Doctrine1KnownSettingsEnum::CONNECTION_NAME);

        return new LogicalConnection($connectionName, $dsn, $manager);
    }
}
