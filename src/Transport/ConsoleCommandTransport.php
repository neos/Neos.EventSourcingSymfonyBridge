<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Transport;

use Neos\EventSourcing\SymfonyBridge\Command\InternalCatchUpEventListenerCommand;
use Neos\EventSourcing\SymfonyBridge\Exception\TransportException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class ConsoleCommandTransport implements AsyncTransportInterface
{
    private $projectDir = '';

    public function __construct(
        string $projectDir
    )
    {
        $this->projectDir = $projectDir;
    }

    public function send(
        string $listenerClassName,
        string $eventStoreContainerId
    ): void
    {

        $command = sprintf(
            '%s/bin/console',
            $this->projectDir
        );

        $process = new Process(
            [
                'php',
                $command,
                InternalCatchUpEventListenerCommand::getDefaultName(),
                $listenerClassName,
                $eventStoreContainerId
            ]
        );
        $process->run();

        $errOutput = $process->getErrorOutput();
        if ('' !==  $errOutput) {
            throw new TransportException($errOutput, 1609257060);
        }
    }
}
