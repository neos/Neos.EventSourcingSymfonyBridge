<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Driver;

use Doctrine\DBAL\Schema\AbstractSchemaManager;

interface Connection extends \Doctrine\DBAL\Driver\Connection
{
    public function getSchemaManager(): AbstractSchemaManager;

    public function getParams();

    public function getDatabasePlatform();

    public function getDatabase();

    public function getHost();
}