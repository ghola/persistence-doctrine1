<?php

namespace spec\PSB\Persistence\Doctrine1\Outbox;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Outbox\OutboxMessage;
use PSB\Persistence\Doctrine1\Outbox\Doctrine1OutboxStorage;
use PSB\Persistence\Doctrine1\Outbox\OutboxMessageConverter;
use PSB\Persistence\Doctrine1\Outbox\OutboxPersister;

/**
 * @mixin Doctrine1OutboxStorage
 */
class Doctrine1OutboxStorageSpec extends ObjectBehavior
{
    /**
     * @var OutboxPersister
     */
    private $persisterMock;

    /**
     * @var OutboxMessageConverter
     */
    private $messageConverterMock;

    private $endpointId = 'irrelevnat';

    public function let(OutboxPersister $persister, OutboxMessageConverter $messageConverter)
    {
        $this->persisterMock = $persister;
        $this->messageConverterMock = $messageConverter;
        $this->beConstructedWith($persister, $messageConverter, $this->endpointId);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Persistence\Doctrine1\Outbox\Doctrine1OutboxStorage');
    }

    function it_gets_a_message_from_storage_if_it_exists(OutboxMessage $message, $storageRecord)
    {
        $messageId = 'irrelevant';
        $this->persisterMock->get($this->endpointId, $messageId)->willReturn([$storageRecord]);
        $this->messageConverterMock->fromDatabaseArray([$storageRecord])->willReturn($message);

        $this->get($messageId)->shouldReturn($message);
    }

    function it_returns_null_when_getting_a_message_from_storage_if_message_does_not_exist()
    {
        $messageId = 'irrelevant';
        $this->persisterMock->get($this->endpointId, $messageId)->willReturn(null);

        $this->get($messageId)->shouldReturn(null);
    }

    function it_stores_a_message(OutboxMessage $message, $storageRecord)
    {
        $this->messageConverterMock->toDatabaseArray($message)->willReturn([$storageRecord]);

        $this->persisterMock->store($this->endpointId, [$storageRecord])->shouldBeCalled();

        $this->store($message);
    }

    function it_marks_a_message_as_dispatched()
    {
        $messageId = 'irrelevant';
        $this->persisterMock->markAsDispatched($this->endpointId, $messageId)->shouldBeCalled();

        $this->markAsDispatched($messageId);
    }

    function it_begins_a_transaction()
    {
        $this->persisterMock->beginTransaction()->shouldBeCalled();

        $this->beginTransaction();
    }

    function it_commits_a_transaction()
    {
        $this->persisterMock->commit()->shouldBeCalled();

        $this->commit();
    }

    function it_rolls_back_a_transaction()
    {
        $this->persisterMock->rollBack()->shouldBeCalled();

        $this->rollBack();
    }
}
