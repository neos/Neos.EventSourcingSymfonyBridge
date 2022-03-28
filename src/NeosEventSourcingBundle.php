<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge;

use Neos\EventSourcing\SymfonyBridge\DependencyInjection\NeosEventSourcingExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class NeosEventSourcingBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new NeosEventSourcingExtension();
    }
}
