<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event;

use Neos\EventSourcing\Event\DomainEventInterface;

class SymfonyBridgeWasCreated implements DomainEventInterface
{
    private $author;

    public function __construct(
        string $author
    )
    {
        $this->author = $author;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }
}
