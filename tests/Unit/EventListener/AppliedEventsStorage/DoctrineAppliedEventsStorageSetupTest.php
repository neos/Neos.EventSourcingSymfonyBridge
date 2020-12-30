<?php

declare(strict_types=1);

namespace Neos\EventSourcing\Tests\SymfonyBridge\Unit\EventListener\AppliedEventsStorage;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Neos\Error\Messages\Result;
use Neos\EventSourcing\SymfonyBridge\Driver\Connection;
use Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage\DoctrineAppliedEventsStorageSetup;
use PHPUnit\Framework\TestCase;

class DoctrineAppliedEventsStorageSetupTest extends TestCase
{
    private $method;
    private $connection;

    public function setUp()
    {
        $className = DoctrineAppliedEventsStorageSetup::class;
        $reflection = new \ReflectionClass($className);

        $this->method = $reflection->getMethod('statusForEventsLogTable');
        $this->method->setAccessible(true);

        $this->connection = $this->createMock(Connection::class);
        $this->connection->expects($this->any())
            ->method('getDatabase')
            ->willReturn('database');
        $this->connection->expects($this->any())
            ->method('getHost')
            ->willReturn('localhost');
    }

    /**
     * @test
     */
    public function statusForEventsLogTableReturnsTableAlreadyExists()
    {
        // given a database with the needed table
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())
            ->method('tablesExist')
            ->willReturn(true);

        $this->connection->expects($this->any())
            ->method('getSchemaManager')
            ->willReturn($schemaManager);

        // when we ask for the status of the table
        $result = $this->method->invokeArgs(
            new DoctrineAppliedEventsStorageSetup($this->connection),
            [
                $this->connection
            ]
        );
        /* @var $result Result */

        // then the table does exists
        $this->assertEquals(
            'Table "neos_eventsourcing_eventlistener_appliedeventslog" (already exists)',
            $result->getFirstNotice()->render()
        );
    }

    /**
     * @test
     */
    public function statusForEventsLogTableReturnsTableDoesNotExists()
    {
        // given a database without the needed table
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())
            ->method('tablesExist')
            ->willReturn(false);

        $this->connection->expects($this->any())
            ->method('getSchemaManager')
            ->willReturn($schemaManager);

        // when we ask for the status of the table
        $result = $this->method->invokeArgs(
            new DoctrineAppliedEventsStorageSetup($this->connection),
            [
                $this->connection
            ]
        );
        /* @var $result Result */

        // then the table doesn't exists
        $this->assertEquals(
            'Creating database table "neos_eventsourcing_eventlistener_appliedeventslog" in database "database" on host localhost....',
            $result->getFirstNotice()->render()
        );
    }
}
