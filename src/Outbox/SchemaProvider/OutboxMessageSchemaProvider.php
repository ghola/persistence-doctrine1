<?php
namespace PSB\Persistence\Doctrine1\Outbox\SchemaProvider;


use PSB\Persistence\Doctrine1\Schema;
use PSB\Persistence\Doctrine1\SchemaProviderInterface;

class OutboxMessageSchemaProvider implements SchemaProviderInterface
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
                    'endpoint_id' => ['type' => 'integer', 'notnull' => true],
                    'message_id' => ['type' => 'string', 'length' => 32, 'fixed' => 1, 'notnull' => true],
                    'is_dispatched' => ['type' => 'boolean', 'notnull' => true],
                    'dispatched_at' => ['type' => 'timestamp'],
                    'transport_operations' => ['type' => 'clob', 'notnull' => true]
                ],
                [
                    'indexes' => [
                        'me' => [
                            'fields' => [
                                'message_id' => [],
                                'endpoint_id' => []
                            ],
                            'type' => 'unique'
                        ],
                        'di' => [
                            'fields' => [
                                'dispatched_at' => [],
                                'is_dispatched' => []
                            ]
                        ]
                    ]
                ]
            );
        }

        return $this->schemas[$tableName];
    }
}
