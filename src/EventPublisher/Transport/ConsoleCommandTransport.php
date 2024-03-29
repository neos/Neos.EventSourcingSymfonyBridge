<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport;

use Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Console\Exception\TransportException;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Console\InternalCatchUpEventListenerCommand;
use Symfony\Component\Process\Process;

/**
 * This is one of the
 */
class ConsoleCommandTransport implements AsyncTransportInterface
{
    private string $projectDir = '';
    private string $environment = '';

    public function __construct(
        string $projectDir,
        string $environment
    ) {
        $this->projectDir = $projectDir;
        $this->environment = $environment;
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
                $eventStoreContainerId,
                '--env',
                $this->environment,
                '-v'
            ]
        );
        $process->run();


        $errOutput = $process->getErrorOutput();
        if ('' !==  $errOutput) {
            throw new TransportException($errOutput, 1609257060);
        }
    }
}
