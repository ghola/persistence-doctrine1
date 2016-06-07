<?php
namespace PSB\Persistence\Doctrine1\Outbox;


use PSB\Persistence\Doctrine1\LogicalConnection;

class OutboxPersister
{
    /**
     * @var LogicalConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $endpointsTableName;

    /**
     * @var string
     *
     */
    private $messagesTableName;

    /**
     * @param LogicalConnection $connection
     * @param string            $messagesTableName
     * @param string            $endpointsTableName
     */
    public function __construct(
        LogicalConnection $connection,
        $messagesTableName,
        $endpointsTableName
    ) {
        $this->connection = $connection;
        $this->endpointsTableName = $endpointsTableName;
        $this->messagesTableName = $messagesTableName;
    }

    /**
     * Attempts to reconnect once if disconnected.
     *
     * @param int    $endpointId
     * @param string $messageId
     *
     * @return array
     * @throws \Exception
     */
    public function get($endpointId, $messageId)
    {
        try {
            $result = $this->connection->executeQuery(
                "SELECT * FROM {$this->messagesTableName} WHERE endpoint_id = ? AND message_id = ?",
                [$endpointId, $this->stripDashes($messageId)]
            )->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw $this->connection->reconnectIfNeeded($e);
        }

        if (!$result) {
            return null;
        }

        unset($result['id']);
        unset($result['dispatched_at']);
        unset($result['endpoint_id']);

        return $result;
    }

    /**
     * @param int   $endpointId
     * @param array $outboxRecord
     *
     * @throws \Exception
     */
    public function store($endpointId, array $outboxRecord)
    {
        $outboxRecord['message_id'] = $this->stripDashes($outboxRecord['message_id']);
        $outboxRecord['endpoint_id'] = $endpointId;
        $outboxRecord['is_dispatched'] = 0;

        $this->connection->transactional(
            function (LogicalConnection $connection) use ($outboxRecord) {
                $connection->insert($this->messagesTableName, $outboxRecord);
            }
        );
    }

    /**
     * Attempts to reconnect once if disconnected.
     *
     * @param int    $endpointId
     * @param string $messageId
     *
     * @throws \Exception
     */
    public function markAsDispatched($endpointId, $messageId)
    {
        try {
            $this->connection->executeUpdate(
                "UPDATE {$this->messagesTableName}
                     SET is_dispatched = 1, dispatched_at = ?, transport_operations = ''
                     WHERE endpoint_id = ? AND message_id = ? AND is_dispatched = 0",
                [
                    (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                    $endpointId,
                    $this->stripDashes($messageId)
                ]
            );
        } catch (\Exception $e) {
            throw $this->connection->reconnectIfNeeded($e);
        }
    }

    /**
     * Initiates the transaction
     * Attempts to reconnect once if disconnected.
     *
     * @throws \Exception
     */
    public function beginTransaction()
    {
        try {
            $this->connection->beginTransaction();
        } catch (\Exception $e) {
            throw $this->connection->reconnectIfNeeded($e);
        }
    }

    /**
     * Commits the transaction
     *
     * Does not attempt to reconnect if disconnected because the transaction would be broken anyway.
     * Reconnection should be done by rollBack.
     *
     * @return void
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * Rolls back the transaction.
     * Attempts to reconnect once if disconnected.
     *
     * @throws \Exception
     */
    public function rollBack()
    {
        try {
            $this->connection->rollBack();;
        } catch (\Exception $e) {
            throw $this->connection->reconnectIfNeeded($e);
        }
    }

    /**
     * Attempts to reconnect once if disconnected.
     *
     * @param \DateTime $dateTime
     *
     * @throws \Exception
     */
    public function removeEntriesOlderThan(\DateTime $dateTime)
    {
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $this->connection->executeUpdate(
            "DELETE FROM {$this->messagesTableName} WHERE is_dispatched = 1 AND dispatched_at <= ?",
            [$dateTime->format('Y-m-d H:i:s')]
        );
    }

    /**
     * @param string $endpointName
     *
     * @return int
     */
    public function fetchOrGenerateEndpointId($endpointName)
    {
        $endpointId = 0;
        $this->connection->transactional(
            function (LogicalConnection $connection) use ($endpointName, &$endpointId) {
                $lookupHash = md5($endpointName);
                $endpointRecord = $connection->executeQuery(
                    "SELECT * FROM {$this->endpointsTableName} WHERE lookup_hash = ?",
                    [$lookupHash]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$endpointRecord) {
                    $connection->insert(
                        $this->endpointsTableName,
                        ['lookup_hash' => $lookupHash, 'name' => $endpointName]
                    );
                    $endpointId = (int)$connection->lastInsertId();
                } else {
                    $endpointId = (int)$endpointRecord['id'];
                }

            }
        );

        return $endpointId;
    }

    /**
     * @param string $messageId
     *
     * @return string
     */
    private function stripDashes($messageId)
    {
        return str_replace('-', '', $messageId);
    }
}
