<?php
namespace specsupport\PSB\Persistence\Doctrine1;


use commonsupport\PSB\Persistence\Doctrine1\SchemaHelperInterface;
use PSB\Persistence\Doctrine1\LogicalConnection;

interface DatabaseAwareSpecInterface
{
    /**
     * @param LogicalConnection $connection
     */
    public function setConnection(LogicalConnection $connection);

    /**
     * @param SchemaHelperInterface $schemaHelper
     */
    public function setSchemaHelper(SchemaHelperInterface $schemaHelper);
}
