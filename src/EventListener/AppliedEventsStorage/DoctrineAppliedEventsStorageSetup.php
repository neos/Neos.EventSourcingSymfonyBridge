<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Types\Types;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\EventSourcing\EventListener\AppliedEventsStorage\AppliedEventsLog;
use Neos\EventSourcing\SymfonyBridge\Driver\Connection;

class DoctrineAppliedEventsStorageSetup
{
    /**
     * @var Connection
     */
    protected $connection;

    protected $schemaManager;

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
     * @throws DBALException | \Throwable
     */
    public function setup(): Result
    {
        $result = $this->retrieveSchemaManager($this->connection);
        if ($result->hasErrors()) {
            return $result;
        }

        $statusForEventsLogTable = $this->statusForEventsLogTable($this->connection);
        if ($statusForEventsLogTable->hasErrors()) {
            return $statusForEventsLogTable;
        }

        $result->merge($statusForEventsLogTable);

        $statements = $this->createSchemaDifferenceStatements($this->connection);
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

    private function retrieveSchemaManager(
        Connection $connection
    )
    {
        $result = new Result();

        $schemaManager = $connection->getSchemaManager();
        if ($schemaManager === null) {
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

        return $result;
    }

    private function statusForEventsLogTable(
        Connection $connection
    ): Result
    {
        $result = new Result();
        try {
            $tableExists = $connection
                ->getSchemaManager()
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
                        $this->connection->getHost()
                    ]
                )
            );
        }

        return $result;
    }

    private function createSchemaDifferenceStatements(
        Connection $connection
    ): array
    {
        $schemaManager = $connection->getSchemaManager();
        $fromSchema = $schemaManager->createSchema();
        $schemaDiff = (new Comparator())->compare($fromSchema, $this->createEventStoreSchema());

        return $schemaDiff->toSaveSql(
            $this->connection->getDatabasePlatform()
        );
    }

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

    private function saveAppliedEventsLog(
        array $statements,
        Result $result
    ): Result
    {
        try {
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
                $this->connection->exec($statement);
            }
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
        return $result;
    }
}