<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Unit\EventListener\AppliedEventsStorage;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Neos\Error\Messages\Result;
use Doctrine\DBAL\Connection;
use Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage\DoctrineAppliedEventsStorageSetup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class DoctrineAppliedEventsStorageSetupTest extends TestCase
{
    private ReflectionMethod $method;
    private MockObject $connection;

    /**
     * @throws ReflectionException
     */
    public function setUp(): void
    {
        $className = DoctrineAppliedEventsStorageSetup::class;
        $reflection = new ReflectionClass($className);

        $this->method = $reflection->getMethod('statusForEventsLogTable');
        $this->method->setAccessible(true);

        $this->connection = $this->createMock(Connection::class);
        $this->connection->expects($this->any())
            ->method('getDatabase')
            ->willReturn('database');
        $this->connection->expects($this->any())
            ->method('getParams')
            ->willReturn(['host' => 'localhost']);
    }

    /**
     * @test
     * @throws ReflectionException
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
                $schemaManager
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
     * @throws ReflectionException
     * @throws Exception
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
        /* @var $result Result */
        $result = $this->method->invokeArgs(
            new DoctrineAppliedEventsStorageSetup($this->connection),
            [
                $this->connection->createSchemaManager()
            ]
        );

        // then the table doesn't exists
        $this->assertEquals(
            'Creating database table "neos_eventsourcing_eventlistener_appliedeventslog" in database "database" on host localhost....',
            $result->getFirstNotice()->render()
        );
    }
}
