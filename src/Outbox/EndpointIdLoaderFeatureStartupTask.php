<?php
namespace PSB\Persistence\Doctrine1\Outbox;


use PSB\Core\BusContextInterface;
use PSB\Core\Feature\FeatureStartupTaskInterface;
use PSB\Core\Util\Settings;
use PSB\Persistence\Doctrine1\Doctrine1KnownSettingsEnum;

class EndpointIdLoaderFeatureStartupTask implements FeatureStartupTaskInterface
{
    /**
     * @var OutboxPersister
     */
    private $outboxPersister;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var string
     */
    private $endpointName;

    /**
     * @param OutboxPersister $outboxPersister
     * @param Settings        $settings
     * @param string          $endpointName
     */
    public function __construct(OutboxPersister $outboxPersister, Settings $settings, $endpointName)
    {
        $this->outboxPersister = $outboxPersister;
        $this->settings = $settings;
        $this->endpointName = $endpointName;
    }

    /**
     * @param BusContextInterface $busContext
     */
    public function start(BusContextInterface $busContext)
    {
        $endpointId = $this->outboxPersister->fetchOrGenerateEndpointId($this->endpointName);
        $this->settings->set(Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINT_ID, $endpointId);
    }
}
