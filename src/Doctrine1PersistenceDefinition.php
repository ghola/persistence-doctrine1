<?php
namespace PSB\Persistence\Doctrine1;


use PSB\Core\Feature\FeatureSettingsExtensions;
use PSB\Persistence\Doctrine1\Outbox\Doctrine1OutboxPersistenceFeature;
use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

class Doctrine1PersistenceDefinition extends PersistenceDefinition
{
    /**
     * @param Settings $settings
     *
     * @return Doctrine1PersistenceConfigurator
     */
    public function createConfigurator(Settings $settings)
    {
        return new Doctrine1PersistenceConfigurator($settings);
    }

    public function formalize()
    {
        $this->supports(
            StorageType::OUTBOX(),
            function (Settings $s) {
                FeatureSettingsExtensions::enableFeatureByDefault(
                    Doctrine1OutboxPersistenceFeature::class,
                    $s
                );
            }
        );
    }
}
