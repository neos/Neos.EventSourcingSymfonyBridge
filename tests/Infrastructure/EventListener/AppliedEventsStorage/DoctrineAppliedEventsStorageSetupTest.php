<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Infrastructure\EventListener\AppliedEventsStorage;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Neos\EventSourcing\EventListener\AppliedEventsStorage\AppliedEventsLog;
use Doctrine\DBAL\Connection;
use Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage\DoctrineAppliedEventsStorageSetup;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

/**
 * @requires extension pdo_sqlite
 */
class DoctrineAppliedEventsStorageSetupTest extends KernelTestCase
{

    /**
     * @var Connection
     */
    private Connection $connection;

    private string $statement = "
        CREATE TABLE neos_eventsourcing_eventlistener_appliedeventslog 
            (
                eventlisteneridentifier VARCHAR(255) NOT NULL, 
                highestappliedsequencenumber INT NOT NULL, 
                PRIMARY KEY(eventlisteneridentifier)
            ) DEFAULT CHARACTER SET utf8mb4COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB
    ";

    protected function setUp(): void
    {
        /** @var EntityManagerInterface $entityManager */
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->connection = $entityManager->getConnection();

        $this->connection->executeStatement('DROP TABLE IF EXISTS neos_eventsourcing_eventlistener_appliedeventslog');
    }

    /**
     * @test
     * @throws Exception
     * @throws Throwable
     */
    public function createLogTableIfTheTableDoesNotExists()
    {
        // given a database without the AppliedEventsLog::TABLE_NAME
        $tableExists = false;

        // when the applied events log setup method is called
        $doctrineAppliedEventsStorageSetup = new DoctrineAppliedEventsStorageSetup($this->connection);
        $doctrineAppliedEventsStorageSetup->setup();

        // then the AppliedEventsLog::TABLE_NAME is created
        $schemaManager = $this->connection->createSchemaManager();
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
     * @throws Exception
     * @throws ReflectionException
     */
    public function createSchemaReturnsTheExpectedSql()
    {
        // given a database without the AppliedEventsLog::TABLE_NAME
        $doctrineAppliedEventsStorageSetup = new DoctrineAppliedEventsStorageSetup($this->connection);

        $className = get_class($doctrineAppliedEventsStorageSetup);
        $reflection = new ReflectionClass($className);

        $method = $reflection->getMethod('createSchemaDifferenceStatements');
        $method->setAccessible(true);

        // when the createSchemaDifferenceStatements is called
        $statements = $method->invokeArgs(
            $doctrineAppliedEventsStorageSetup,
            [$this->connection->createSchemaManager()]
        );

        // then the expected statements are returned
        $this->assertEquals(
            preg_replace('/\s+/', '', $this->statement),
            str_replace(' ', '', $statements[0])
        );
    }
}
