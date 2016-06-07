<?php
namespace specsupport\PSB\Persistence\Doctrine1\PhpSpec;


use commonsupport\PSB\Persistence\Doctrine1\SchemaHelper;
use commonsupport\PSB\Persistence\Doctrine1\SchemaHelperInterface;
use PhpSpec\ObjectBehavior;
use PSB\Persistence\Doctrine1\LogicalConnection;
use specsupport\PSB\Persistence\Doctrine1\DatabaseAwareSpecInterface;

abstract class DatabaseAwareSpec extends ObjectBehavior implements DatabaseAwareSpecInterface
{
    /**
     * @var LogicalConnection
     */
    protected $connection;

    /**
     * @var SchemaHelper
     */
    protected $schemaHelper;

    /**
     * @param LogicalConnection $connection
     */
    public function setConnection(LogicalConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param SchemaHelperInterface $schemaHelper
     */
    public function setSchemaHelper(SchemaHelperInterface $schemaHelper)
    {
        $this->schemaHelper = $schemaHelper;
    }
}
