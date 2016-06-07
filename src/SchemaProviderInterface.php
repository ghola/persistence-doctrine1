<?php
namespace PSB\Persistence\Doctrine1;


interface SchemaProviderInterface
{
    /**
     * @param string $tableName
     *
     * @return Schema
     */
    public function getSchemaFor($tableName);
}
