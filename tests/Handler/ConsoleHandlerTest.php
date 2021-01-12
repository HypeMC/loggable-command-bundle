<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\Handler;

use Bizkit\LoggableCommandBundle\Handler\ConsoleHandler;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Logger;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Bizkit\LoggableCommandBundle\Handler\ConsoleHandler
 *
 * @phpstan-import-type Level from \Monolog\Logger
 */
final class ConsoleHandlerTest extends TestCase
{
    /**
     * @dataProvider outputData
     *
     * @phpstan-param Level $level
     */
    public function testCorrectOutputIsUsed(string $expectedOutput, string $silentOutput, int $stdErrThreshold, int $level): void
    {
        $errorOutput = $this->createMock(OutputInterface::class);
        $errorOutput->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_DEBUG);

        $standardOutput = $this->createMock(ConsoleOutputInterface::class);
        $standardOutput->method('getErrorOutput')->willReturn($errorOutput);
        $standardOutput->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_DEBUG);

        $event = new ConsoleCommandEvent(null, $this->createMock(InputInterface::class), $standardOutput);

        $consoleHandler = new ConsoleHandler();
        $consoleHandler->onCommand($event);

        ${$expectedOutput}->expects($this->once())
            ->method('write')
            ->with($this->stringContains('Lorem ipsum'), false, OutputInterface::VERBOSITY_DEBUG)
        ;

        ${$silentOutput}->expects($this->never())
            ->method('write')
        ;

        $consoleHandler->setStdErrThreshold($stdErrThreshold);

        $consoleHandler->handle([
            'datetime' => new \DateTimeImmutable('2021-01-31 13:50:02'),
            'channel' => 'foo',
            'message' => 'Lorem ipsum',
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'context' => [],
            'extra' => [],
        ]);
    }

    public function outputData(): iterable
    {
        yield 'NOTICE with threshold level WARNING' => [
            'standardOutput',
            'errorOutput',
            Logger::WARNING,
            Logger::NOTICE,
        ];
        yield 'WARNING with threshold level WARNING' => [
            'errorOutput',
            'standardOutput',
            Logger::WARNING,
            Logger::WARNING,
        ];
        yield 'ERROR with threshold level WARNING' => [
            'errorOutput',
            'standardOutput',
            Logger::WARNING,
            Logger::WARNING,
        ];
        yield 'WARNING with threshold level ERROR' => [
            'standardOutput',
            'errorOutput',
            Logger::ERROR,
            Logger::WARNING,
        ];
        yield 'CRITICAL with threshold level ERROR' => [
            'errorOutput',
            'standardOutput',
            Logger::ERROR,
            Logger::CRITICAL,
        ];
    }
}
