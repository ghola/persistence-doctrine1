<?php
namespace PSB\Persistence\Doctrine1\Outbox;


use PSB\Core\Feature\FeatureInstallTaskInterface;
use PSB\Persistence\Doctrine1\LogicalConnection;
use PSB\Persistence\Doctrine1\Outbox\SchemaProvider\OutboxEndpointSchemaProvider;
use PSB\Persistence\Doctrine1\Outbox\SchemaProvider\OutboxMessageSchemaProvider;

class OutboxTablesCreatorFeatureInstallTask implements FeatureInstallTaskInterface
{
    /**
     * @var LogicalConnection
     */
    private $connection;

    /**
     * @var OutboxEndpointSchemaProvider
     */
    private $endpointSchemaProvider;

    /**
     * @var OutboxMessageSchemaProvider
     */
    private $messagesSchemaProvider;

    /**
     * @var string
     */
    private $endpointsTableName;

    /**
     * @var string
     */
    private $messagesTableName;

    /**
     * @param LogicalConnection            $connection
     * @param OutboxEndpointSchemaProvider $endpointSchemaProvider
     * @param OutboxMessageSchemaProvider  $messagesSchemaProvider
     * @param string                       $endpointsTableName
     * @param string                       $messagesTableName
     */
    public function __construct(
        LogicalConnection $connection,
        OutboxEndpointSchemaProvider $endpointSchemaProvider,
        OutboxMessageSchemaProvider $messagesSchemaProvider,
        $endpointsTableName,
        $messagesTableName
    ) {
        $this->connection = $connection;
        $this->endpointSchemaProvider = $endpointSchemaProvider;
        $this->messagesSchemaProvider = $messagesSchemaProvider;
        $this->endpointsTableName = $endpointsTableName;
        $this->messagesTableName = $messagesTableName;
    }

    public function install()
    {
        $messageSchema = $this->messagesSchemaProvider->getSchemaFor($this->messagesTableName);
        $endpointSchema = $this->endpointSchemaProvider->getSchemaFor($this->endpointsTableName);

        // is there a better way to atomically create in order to not compete with other endpoints?
        try {
            $this->connection->createTable(
                $messageSchema->getTableName(),
                $messageSchema->getDefinition(),
                $messageSchema->getOptions()
            );
        } catch (\Exception $e) {
        }

        try {
            $this->connection->createTable(
                $endpointSchema->getTableName(),
                $endpointSchema->getDefinition(),
                $endpointSchema->getOptions()
            );
        } catch (\Exception $e) {
        }
    }
}
