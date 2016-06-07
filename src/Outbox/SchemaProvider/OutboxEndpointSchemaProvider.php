<?php
namespace PSB\Persistence\Doctrine1\Outbox\SchemaProvider;


use PSB\Persistence\Doctrine1\Schema;
use PSB\Persistence\Doctrine1\SchemaProviderInterface;

class OutboxEndpointSchemaProvider implements SchemaProviderInterface
{
    /**
     * @var Schema[]
     */
    private $schemas = [];

    /**
     * @param string $tableName
     *
     * @return Schema
     */
    public function getSchemaFor($tableName)
    {
        if (!isset($this->schemas[$tableName])) {
            $this->schemas[$tableName] = new Schema(
                $tableName,
                [
                    'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
                    'lookup_hash' => ['type' => 'string', 'length' => 32, 'fixed' => 1, 'notnull' => true],
                    'name' => ['type' => 'string', 'notnull' => true]
                ],
                [
                    'indexes' => [
                        'lookup_hash' => [
                            'fields' => [
                                'lookup_hash' => []
                            ],
                            'type' => 'unique'
                        ]
                    ]
                ]
            );
        }

        return $this->schemas[$tableName];
    }
}
