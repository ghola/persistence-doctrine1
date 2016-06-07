<?php

namespace spec\PSB\Persistence\Doctrine1\Outbox;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Persistence\Doctrine1\LogicalConnection;
use PSB\Persistence\Doctrine1\Outbox\OutboxTablesCreatorFeatureInstallTask;
use PSB\Persistence\Doctrine1\Outbox\SchemaProvider\OutboxEndpointSchemaProvider;
use PSB\Persistence\Doctrine1\Outbox\SchemaProvider\OutboxMessageSchemaProvider;
use PSB\Persistence\Doctrine1\Schema;

/**
 * @mixin OutboxTablesCreatorFeatureInstallTask
 */
class OutboxTablesCreatorFeatureInstallTaskSpec extends ObjectBehavior
{
    /**
     * @var LogicalConnection
     */
    private $connectionMock;

    /**
     * @var OutboxEndpointSchemaProvider
     */
    private $endpointSchemaProviderMock;

    /**
     * @var OutboxMessageSchemaProvider
     */
    private $messagesSchemaProviderMock;

    private $endpointsTableName = 'endpoints_table';

    private $messagesTableName = 'messages_table';

    function let(
        LogicalConnection $connection,
        OutboxEndpointSchemaProvider $endpointSchemaProvider,
        OutboxMessageSchemaProvider $messagesSchemaProvider
    ) {
        $this->connectionMock = $connection;
        $this->endpointSchemaProviderMock = $endpointSchemaProvider;
        $this->messagesSchemaProviderMock = $messagesSchemaProvider;
        $this->beConstructedWith(
            $connection,
            $endpointSchemaProvider,
            $messagesSchemaProvider,
            $this->endpointsTableName,
            $this->messagesTableName
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Persistence\Doctrine1\Outbox\OutboxTablesCreatorFeatureInstallTask');
    }

    function it_creates_the_tables_for_all_outbox_schemas()
    {
        $messageSchema = new Schema($this->messagesTableName, [], []);
        $endpointSchema = new Schema($this->endpointsTableName, [], []);
        $this->messagesSchemaProviderMock->getSchemaFor($this->messagesTableName)->willReturn($messageSchema);
        $this->endpointSchemaProviderMock->getSchemaFor($this->endpointsTableName)->willReturn($endpointSchema);

        $this->connectionMock->createTable($this->messagesTableName, [], [])->shouldBeCalled();
        $this->connectionMock->createTable($this->endpointsTableName, [], [])->shouldBeCalled();

        $this->install();
    }

    function it_does_not_throw_if_tables_already_exist(\Doctrine_Export $export)
    {
        $messageSchema = new Schema($this->messagesTableName, [], []);
        $endpointSchema = new Schema($this->endpointsTableName, [], []);
        $this->messagesSchemaProviderMock->getSchemaFor($this->messagesTableName)->willReturn($messageSchema);
        $this->endpointSchemaProviderMock->getSchemaFor($this->endpointsTableName)->willReturn($endpointSchema);

        $this->connectionMock->createTable($this->messagesTableName, [], [])->willThrow(\Exception::class);
        $this->connectionMock->createTable($this->endpointsTableName, [], [])->willThrow(\Exception::class);

        $this->shouldNotThrow()->duringInstall();
    }
}
