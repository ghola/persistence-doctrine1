<?php

namespace spec\PSB\Persistence\Doctrine1;

use Prophecy\Argument;
use PSB\Core\Persistence\StorageType;
use PSB\Persistence\Doctrine1\LogicalConnection;
use specsupport\PSB\Persistence\Doctrine1\PhpSpec\DatabaseAwareSpec;

/**
 * @mixin LogicalConnection
 */
class LogicalConnectionSpec extends DatabaseAwareSpec
{
    public function let()
    {
        $this->beConstructedWith(
            $this->connection->getName(),
            $this->connection->getDsn(),
            $this->connection->getManager()
        );
        $this->schemaHelper->clean(StorageType::OUTBOX);
    }

    public function it_throws_if_dsn_is_empty_or_null_on_construction()
    {
        $this->beConstructedWith(null, '', $this->connection->getManager());
        $this->shouldThrow()->duringInstantiation();
    }

    function it_rolls_back_completely_by_resetting_the_transaction_nesting_level_if_multiple_transactions_have_begun()
    {
        $this->connect();
        $this->beginTransaction();
        $this->beginTransaction();
        $this->beginTransaction();
        $this->beginTransaction();
        $this->getWrappedConnection()->rollback();
        $this->rollBack();

        $this->getTransactionNestingLevel()->shouldBe(0);
    }

    function it_rolls_back_completely_by_resetting_the_transaction_nesting_level_if_one_transaction_has_begun()
    {
        $this->connect();
        $this->beginTransaction();
        $this->rollBack();

        $this->getTransactionNestingLevel()->shouldBe(0);
    }
}
