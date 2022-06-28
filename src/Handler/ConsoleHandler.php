<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler as BaseConsoleHandler;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

if (Logger::API >= 3) {
    trait CompatibilityHandlerTrait
    {
        public function handle(LogRecord $record): bool
        {
            return $this->doHandle($record);
        }

        public function isHandling(LogRecord $record): bool
        {
            return $this->doIsHandling($record);
        }

        public function getLevel(): Level
        {
            return $this->doGetLevel();
        }
    }
} else {
    trait CompatibilityHandlerTrait
    {
        public function handle(array $record): bool
        {
            return $this->doHandle($record);
        }

        public function isHandling(array $record): bool
        {
            return $this->doIsHandling($record);
        }

        public function getLevel(): int
        {
            return $this->doGetLevel();
        }
    }
}

/**
 * Unlike Symfony's ConsoleHandler which sends everything to stderr if available,
 * this one sends to stdout or stderr depending on the {@see ConsoleHandler::$stdErrThreshold} setting.
 */
final class ConsoleHandler extends AbstractHandler implements ProcessableHandlerInterface, FormattableHandlerInterface, EventSubscriberInterface
{
    use CompatibilityHandlerTrait;

    /**
     * @var BaseConsoleHandler
     */
    private $innerHandler;

    /**
     * @var int
     */
    private $stdErrThreshold;

    /**
     * @var OutputInterface|null
     */
    private $standardOutput;

    /**
     * @var OutputInterface|null
     */
    private $errorOutput;

    public function __construct(BaseConsoleHandler $innerHandler)
    {
        $this->innerHandler = $innerHandler;
        $this->setStdErrThreshold(Logger::toMonologLevel(LogLevel::WARNING));
    }

    /**
     * @param int|Level $stdErrThreshold
     */
    public function setStdErrThreshold($stdErrThreshold): void
    {
        $this->stdErrThreshold = $stdErrThreshold instanceof Level ? $stdErrThreshold->value : $stdErrThreshold;
    }

    public function setStandardOutput(OutputInterface $standardOutput): void
    {
        $this->standardOutput = $standardOutput;
    }

    public function setErrorOutput(OutputInterface $output): void
    {
        $this->errorOutput = $output;
    }

    public function close(): void
    {
        $this->standardOutput = null;
        $this->errorOutput = null;

        $this->innerHandler->close();
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->innerHandler->setOutput($output);
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->innerHandler->onCommand($event);

        $output = $event->getOutput();
        $this->setStandardOutput($output);

        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $this->setErrorOutput($output);
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        $this->innerHandler->onTerminate($event);

        $this->close();
    }

    private function doHandle($record): bool
    {
        $this->innerHandler->setOutput(
            $record['level'] < $this->stdErrThreshold ? $this->standardOutput : $this->errorOutput
        );

        return $this->innerHandler->handle($record);
    }

    public static function getSubscribedEvents(): array
    {
        return BaseConsoleHandler::getSubscribedEvents();
    }

    private function doIsHandling($record): bool
    {
        return $this->innerHandler->isHandling($record);
    }

    public function handleBatch(array $records): void
    {
        $this->innerHandler->handleBatch($records);
    }

    public function pushProcessor($callback): HandlerInterface
    {
        $this->innerHandler->pushProcessor($callback);

        return $this;
    }

    public function popProcessor(): callable
    {
        return $this->innerHandler->popProcessor();
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->innerHandler->setFormatter($formatter);

        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        return $this->innerHandler->getFormatter();
    }

    public function reset(): void
    {
        $this->innerHandler->reset();
    }

    public function setLevel($level): AbstractHandler
    {
        $this->innerHandler->setLevel($level);

        return $this;
    }

    private function doGetLevel()
    {
        return $this->innerHandler->getLevel();
    }

    public function setBubble($bubble): AbstractHandler
    {
        $this->innerHandler->setBubble($bubble);

        return $this;
    }

    public function getBubble(): bool
    {
        return $this->innerHandler->getBubble();
    }
}
