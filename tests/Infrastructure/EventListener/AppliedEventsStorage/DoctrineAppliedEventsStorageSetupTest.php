<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Infrastructure\EventListener\AppliedEventsStorage;

use Neos\EventSourcing\EventListener\AppliedEventsStorage\AppliedEventsLog;
use Neos\EventSourcing\SymfonyBridge\Driver\Connection;
use Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage\DoctrineAppliedEventsStorageSetup;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;

/**
 * @requires extension pdo_sqlite
 */
class DoctrineAppliedEventsStorageSetupTest extends TestCase
{

    /**
     * @var Connection
     */
    private $connection;

    private string $statement = "
        CREATE TABLE neos_eventsourcing_eventlistener_appliedeventslog 
            (
                eventlisteneridentifier VARCHAR(255) NOT NULL, 
                highestappliedsequencenumber INTEGER NOT NULL, 
                PRIMARY KEY(eventlisteneridentifier)
            )
    ";

    protected function setUp(): void
    {
        $entityManager = DoctrineTestHelper::createTestEntityManager();
        $this->connection = $entityManager->getConnection();
    }

    /**
     * @test
     */
    public function createLogTableIfTheTableDoesNotExists()
    {
        $this->connection->beginTransaction();

        // given a database without the AppliedEventsLog::TABLE_NAME
        $tableExists = false;

        // when the applied events log setup method is called
        $doctrineAppliedEventsStorageSetup = new DoctrineAppliedEventsStorageSetup($this->connection);
        $doctrineAppliedEventsStorageSetup->setup();

        // then the AppliedEventsLog::TABLE_NAME is created
        $schemaManager = $this->connection->getSchemaManager();
        if ($schemaManager->tablesExist(array(AppliedEventsLog::TABLE_NAME)) === true) {
            $tableExists = true;
        }

        $this->assertEquals(
            true,
            $tableExists
        );
    }

    /**
     * @test
     */
    public function createSchemaReturnsTheExpectedSql()
    {
        // given a database without the AppliedEventsLog::TABLE_NAME
        $doctrineAppliedEventsStorageSetup = new DoctrineAppliedEventsStorageSetup($this->connection);

        $className = get_class($doctrineAppliedEventsStorageSetup);
        $reflection = new \ReflectionClass($className);

        $method = $reflection->getMethod('createSchemaDifferenceStatements');
        $method->setAccessible(true);

        // when the createSchemaDifferenceStatements is called
        $statements = $method->invokeArgs($doctrineAppliedEventsStorageSetup, [$this->connection]);

        // then the expected statements are returned
        $this->assertEquals(
            preg_replace('/\s+/', '', $this->statement),
            str_replace(' ', '', $statements[0])
        );
    }
}
