<?php

namespace spec\PSB\Persistence\Doctrine1\Outbox;

use commonsupport\PSB\Persistence\Doctrine1\ConnectionKiller;
use PhpSpec\Exception\Example\SkippingException;
use Prophecy\Argument;
use PSB\Core\Persistence\StorageType;
use PSB\Persistence\Doctrine1\Doctrine1KnownSettingsEnum;
use PSB\Persistence\Doctrine1\Outbox\OutboxPersister;
use specsupport\PSB\Persistence\Doctrine1\PhpSpec\DatabaseAwareSpec;

/**
 * @mixin OutboxPersister
 */
class OutboxPersisterSpec extends DatabaseAwareSpec
{
    private $irrelevantEndpointId = 666;

    private $messageId;

    public function let()
    {
        $this->beConstructedWith(
            $this->connection,
            $this->schemaHelper->getTableFor(
                StorageType::OUTBOX,
                Doctrine1KnownSettingsEnum::OUTBOX_MESSAGES_TABLE_NAME
            ),
            $this->schemaHelper->getTableFor(
                StorageType::OUTBOX,
                Doctrine1KnownSettingsEnum::OUTBOX_ENDPOINTS_TABLE_NAME
            )
        );
        $this->schemaHelper->clean(StorageType::OUTBOX);
        $this->messageId = md5('irrelevant');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Persistence\Doctrine1\Outbox\OutboxPersister');
    }

    function it_returns_null_if_getting_a_message_that_does_not_exist()
    {
        $this->get($this->irrelevantEndpointId, $this->messageId)->shouldBe(null);
    }

    function it_stores_a_message_with_qualified_id_and_as_not_dispatched()
    {
        $transportOperations = 'someoperations';

        $this->store(
            $this->irrelevantEndpointId,
            [
                'message_id' => $this->messageId,
                'transport_operations' => $transportOperations
            ]
        );

        $this->get($this->irrelevantEndpointId, $this->messageId)->shouldReturn(
            [
                'message_id' => $this->messageId,
                'is_dispatched' => '0',
                'transport_operations' => $transportOperations
            ]
        );
    }

    function it_throws_when_storing_the_same_message_more_than_once()
    {
        $transportOperations = 'someoperations';

        $this->store(
            $this->irrelevantEndpointId,
            [
                'message_id' => $this->messageId,
                'transport_operations' => $transportOperations
            ]
        );

        $this->shouldThrow()->duringStore(
            $this->irrelevantEndpointId,
            [
                'message_id' => $this->messageId,
                'transport_operations' => $transportOperations
            ]
        );
    }

    function it_marks_the_message_as_dispatched_and_empties_transport_operations()
    {
        $transportOperations = 'someoperations';

        $this->store(
            $this->irrelevantEndpointId,
            [
                'message_id' => $this->messageId,
                'transport_operations' => $transportOperations
            ]
        );

        $this->markAsDispatched($this->irrelevantEndpointId, $this->messageId);

        $this->get($this->irrelevantEndpointId, $this->messageId)->shouldReturn(
            [
                'message_id' => $this->messageId,
                'is_dispatched' => '1',
                'transport_operations' => ''
            ]
        );
    }

    function it_deletes_all_records_which_are_dispatched_and_are_older_than_a_datetime()
    {
        $transportOperations = 'someoperations';

        $this->store(
            $this->irrelevantEndpointId,
            [
                'message_id' => $this->messageId,
                'transport_operations' => $transportOperations
            ]
        );

        $this->markAsDispatched($this->irrelevantEndpointId, $this->messageId);
        $this->removeEntriesOlderThan(new \DateTime('+1 minute', new \DateTimeZone('UTC')));

        $this->get($this->irrelevantEndpointId, $this->messageId)->shouldReturn(null);
    }

    function it_does_not_delete_records_which_are_dispatched_and_are_not_older_than_a_datetime()
    {
        $transportOperations = 'someoperations';

        $this->store(
            $this->irrelevantEndpointId,
            [
                'message_id' => $this->messageId,
                'transport_operations' => $transportOperations
            ]
        );

        $this->markAsDispatched($this->irrelevantEndpointId, $this->messageId);
        $this->removeEntriesOlderThan(new \DateTime('-1 minute', new \DateTimeZone('UTC')));

        $this->get($this->irrelevantEndpointId, $this->messageId)->shouldReturn(
            [
                'message_id' => $this->messageId,
                'is_dispatched' => '1',
                'transport_operations' => ''
            ]
        );
    }

    function it_generates_a_new_endpoint_id_for_an_endpoint_name_if_that_name_does_not_already_have_an_id()
    {
        $endpointName = 'irrelevant';
        $endpointId = $this->fetchOrGenerateEndpointId($endpointName);
        $endpointId->shouldNotBe(0);
        $endpointId->shouldBeInteger();
    }

    function it_gets_the_endpoint_id_based_on_the_endpoint_name_if_that_name_already_has_an_id()
    {
        $endpointName = 'irrelevant';
        $generatedEndpointId = $this->fetchOrGenerateEndpointId($endpointName);
        $fetchedEndpointId = $this->fetchOrGenerateEndpointId($endpointName);
        $fetchedEndpointId->shouldBe($generatedEndpointId->getWrappedObject());
    }

    function it_reconnects_if_disconnected_when_getting_a_message()
    {
        $this->skipIfEmbedded();

        $this->connection->connect();
        ConnectionKiller::killConection($this->connection);
        $this->shouldThrow()->duringGet($this->irrelevantEndpointId, $this->messageId);
        expect($this->connection->ping())->toBe(true);
    }

    function it_reconnects_if_disconnected_when_marking_a_message_as_dispatched()
    {
        $this->skipIfEmbedded();

        $this->connection->connect();
        ConnectionKiller::killConection($this->connection);
        $this->shouldThrow()->duringMarkAsDispatched($this->irrelevantEndpointId, $this->messageId);
        expect($this->connection->ping())->toBe(true);

        try {
            // rollback to fix nesting level for other tests
            $this->rollBack();
        } catch (\Exception $e) {
            // catching because it rethrows
        }
    }

    function it_reconnects_if_disconnected_when_beginning_transaction()
    {
        $this->skipIfEmbedded();

        $this->connection->connect();
        ConnectionKiller::killConection($this->connection);
        $this->shouldThrow()->duringBeginTransaction();
        expect($this->connection->ping())->toBe(true);

        try {
            // rollback to fix nesting level for other tests
            $this->rollBack();
        } catch (\Exception $e) {
            // catching because it rethrows
        }
    }

    function it_rolls_back_and_reconnects_if_disconnected()
    {
        $this->skipIfEmbedded();

        $this->connection->connect();
        ConnectionKiller::killConection($this->connection);
        try {
            $this->rollBack();
        } catch (\Exception $e) {
            // catching because it rethrows
        }

        expect($this->connection->ping())->toBe(true);
    }

    private function skipIfEmbedded()
    {
        if ($this->connection->isEmbeddedDatabase()) {
            throw new SkippingException("Embedded databases do not have interruptible connections.");
        }
    }
}
