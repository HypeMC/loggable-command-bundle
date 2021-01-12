<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Handler;

use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler as BaseConsoleHandler;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Unlike Symfony's ConsoleHandler which sends everything to stderr if available,
 * this one sends to stdout or stderr depending on the {@see ConsoleHandler::$stdErrThreshold} setting.
 */
class ConsoleHandler extends BaseConsoleHandler
{
    /**
     * @var int
     */
    private $stdErrThreshold = Logger::WARNING;

    /**
     * @var OutputInterface|null
     */
    private $standardOutput;

    /**
     * @var OutputInterface|null
     */
    private $errorOutput;

    public function setStdErrThreshold(int $stdErrThreshold): void
    {
        $this->stdErrThreshold = $stdErrThreshold;
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

        parent::close();
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        parent::onCommand($event);

        $output = $event->getOutput();
        $this->setStandardOutput($output);

        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $this->setErrorOutput($output);
    }

    protected function write(array $record): void
    {
        $this->setOutput($record['level'] < $this->stdErrThreshold ? $this->standardOutput : $this->errorOutput);

        parent::write($record);
    }
}
