<?php
namespace commonsupport\PSB\Persistence\Doctrine1;


use PSB\Persistence\Doctrine1\LogicalConnection;

class ConnectionKiller
{
    public static function killConection(LogicalConnection $connection)
    {
        $driverName = $connection->getWrappedConnection()->getDriverName();
        if (stripos($driverName, 'mysql') !== false) {
            static::killMysqlLogicalConnection($connection);
        }
    }

    public static function killMysqlLogicalConnection(LogicalConnection $connection)
    {
        try {
            $connection->executeQuery("KILL CONNECTION_ID()");
        } catch (\Exception $e) {
        }

        /**
         * Looping because just killing does not guarantee it gets killed
         * as per http://dev.mysql.com/doc/refman/5.6/en/kill.html
         */
        $isKilled = false;
        while (!$isKilled) {
            try {
                $connection->executeQuery("SELECT * FROM mysql.help_topic ORDER BY help_topic_id DESC");
            } catch (\Exception $e) {
                $isKilled = true;
            }
        }
    }
}
