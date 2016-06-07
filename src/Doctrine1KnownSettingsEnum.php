<?php
namespace PSB\Persistence\Doctrine1;


class Doctrine1KnownSettingsEnum
{
    const DSN = 'PSB.Doctrine1.DSN';
    const MANAGER = 'PSB.Doctrine1.Manager';
    const CONNECTION_NAME = 'PSB.Doctrine1.ConnectionName';
    const LOGICAL_CONNECTION = 'PSB.Doctrine1.LogicalConnection';
    const OUTBOX_ENDPOINT_ID = 'PSB.Doctrine1.Outbox.EndpointId';
    const OUTBOX_MESSAGES_TABLE_NAME = 'PSB.Doctrine1.Outbox.MessagesTableName';
    const OUTBOX_ENDPOINTS_TABLE_NAME = 'PSB.Doctrine1.Outbox.EndpointsTableName';
}
