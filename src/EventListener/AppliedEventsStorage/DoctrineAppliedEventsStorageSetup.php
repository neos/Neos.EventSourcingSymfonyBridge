<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\EventSourcing\EventListener\AppliedEventsStorage\AppliedEventsLog;
use Throwable;

class DoctrineAppliedEventsStorageSetup
{
    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * DoctrineAppliedEventsStorageSetup constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function setup(): Result
    {
        $result = new Result();
        try {
            $schemaManager = $this->connection->createSchemaManager();
        } catch(\Exception $e) {
            $result->addError(
                new Error(
                    'Failed to retrieve Schema Manager',
                    1592381759,
                    [],
                    'Connection failed'
                )
            );
            return $result;
        }

        $statusForEventsLogTable = $this->statusForEventsLogTable($schemaManager);
        if ($statusForEventsLogTable->hasErrors()) {
            return $statusForEventsLogTable;
        }

        $result->merge($statusForEventsLogTable);

        $statements = $this->createSchemaDifferenceStatements($schemaManager);
        if ($statements === []) {
            $result->addNotice(
                new Notice(
                    'Table schema is up to date, no migration required'
                )
            );
            return $result;
        }

        return $this->saveAppliedEventsLog(
            $statements,
            $result
        );
    }

    /**
     * @throws Exception
     */
    private function statusForEventsLogTable(
        AbstractSchemaManager $schemaManager
    ): Result
    {
        $result = new Result();
        try {
            $tableExists = $schemaManager
                ->tablesExist(
                    [
                        AppliedEventsLog::TABLE_NAME
                    ]
                );
        } catch (ConnectionException $exception) {
            $result->addError(
                new Error(
                    $exception->getMessage(),
                    $exception->getCode(),
                    [],
                    'Connection failed'
                )
            );
            return $result;
        }

        if ($tableExists) {
            $result->addNotice(
                new Notice(
                    'Table "%s" (already exists)',
                    null,
                    [
                        AppliedEventsLog::TABLE_NAME
                    ]
                )
            );
        } else {
            $result->addNotice(
                new Notice(
                    'Creating database table "%s" in database "%s" on host %s....',
                    null,
                    [
                        AppliedEventsLog::TABLE_NAME,
                        $this->connection->getDatabase(),
                        $this->connection->getParams()['host']
                    ]
                )
            );
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private function createSchemaDifferenceStatements(
        AbstractSchemaManager $schemaManager
    ): array
    {
        $fromSchema = $schemaManager->createSchema();
        $schemaDiff = (new Comparator())->compareSchemas($fromSchema, $this->createEventStoreSchema());

        return $schemaDiff->toSaveSql(
            $this->connection->getDatabasePlatform()
        );
    }

    /**
     * @throws SchemaException
     */
    private function createEventStoreSchema(): Schema
    {
        $schemaConfiguration = new SchemaConfig();
        $connectionParameters = $this->connection->getParams();
        if (isset($connectionParameters['defaultTableOptions'])) {
            $schemaConfiguration->setDefaultTableOptions($connectionParameters['defaultTableOptions']);
        }
        $schema = new Schema([], [], $schemaConfiguration);

        $table = $schema->createTable(AppliedEventsLog::TABLE_NAME);
        $table->addColumn('eventlisteneridentifier', Types::STRING, ['length' => 255]);
        $table->addColumn('highestappliedsequencenumber', Types::INTEGER);
        $table->setPrimaryKey(['eventlisteneridentifier']);

        return $schema;
    }

    /**
     * @throws Exception
     */
    private function saveAppliedEventsLog(
        array $statements,
        Result $result
    ): Result
    {
        // NOTE: we are not allowed to wrap this in a transaction, as newer PHP versions will throw Exceptions (at least on MariaDB)
        // when the statement is not executable in a transaction.
        foreach ($statements as $statement) {
            $result->addNotice(
                new Notice(
                    '<info>++</info> %s',
                    null,
                    [
                        $statement
                    ]
                )
            );
            $this->connection->executeStatement($statement);
        }
        return $result;
    }
}
