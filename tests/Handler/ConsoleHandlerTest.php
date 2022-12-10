<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\Handler;

use Bizkit\LoggableCommandBundle\Handler\ConsoleHandler;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use Psr\Log\LogLevel;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler as BaseConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Bizkit\LoggableCommandBundle\Handler\ConsoleHandler
 */
final class ConsoleHandlerTest extends TestCase
{
    public function testHandlerIsDecoratedAsExpected(): void
    {
        $innerConsoleHandler = new BaseConsoleHandler(null, true, [
            OutputInterface::VERBOSITY_NORMAL => LogLevel::WARNING,
        ]);
        $consoleHandler = new ConsoleHandler($innerConsoleHandler);

        $output = $this->createMock(OutputInterface::class);
        $output->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $consoleHandler->setOutput($output);

        $output->expects($this->once())->method('write');
        $consoleHandler->handleBatch([$this->getRecord()]);

        $this->assertFalse($innerConsoleHandler->isHandling($this->getRecord(LogLevel::DEBUG)));
        $this->assertFalse($consoleHandler->isHandling($this->getRecord(LogLevel::DEBUG)));
        $this->assertTrue($innerConsoleHandler->isHandling($this->getRecord(LogLevel::ERROR)));
        $this->assertTrue($consoleHandler->isHandling($this->getRecord(LogLevel::ERROR)));

        $consoleHandler->setBubble(false);
        $this->assertFalse($innerConsoleHandler->getBubble());
        $this->assertFalse($consoleHandler->getBubble());
        $consoleHandler->setBubble(true);
        $this->assertTrue($innerConsoleHandler->getBubble());
        $this->assertTrue($consoleHandler->getBubble());

        $consoleHandler->setLevel(LogLevel::NOTICE);
        $this->assertSame(Logger::toMonologLevel(LogLevel::NOTICE), $innerConsoleHandler->getLevel());
        $this->assertSame(Logger::toMonologLevel(LogLevel::NOTICE), $consoleHandler->getLevel());
        $consoleHandler->setLevel(LogLevel::WARNING);
        $this->assertSame(Logger::toMonologLevel(LogLevel::WARNING), $innerConsoleHandler->getLevel());
        $this->assertSame(Logger::toMonologLevel(LogLevel::WARNING), $consoleHandler->getLevel());

        $consoleHandler->onTerminate(new ConsoleTerminateEvent(
            new Command(), $this->createMock(InputInterface::class), $this->createMock(OutputInterface::class), 0,
        ));
        $this->assertFalse($innerConsoleHandler->isHandling($this->getRecord(LogLevel::ERROR)));
        $this->assertFalse($consoleHandler->isHandling($this->getRecord(LogLevel::ERROR)));

        $formatter = $this->createMock(FormatterInterface::class);
        $consoleHandler->setFormatter($formatter);
        $this->assertSame($formatter, $innerConsoleHandler->getFormatter());
        $this->assertSame($formatter, $consoleHandler->getFormatter());

        $processor = $this->createMock(ProcessorInterface::class);
        $consoleHandler->pushProcessor($processor);
        $this->assertSame($processor, $innerConsoleHandler->popProcessor());
        $consoleHandler->pushProcessor($processor);
        $this->assertSame($processor, $consoleHandler->popProcessor());

        $processor = $this->createMock(DummyResettableProcessor::class);
        $processor->expects($this->exactly(2))->method('reset');
        $consoleHandler->pushProcessor($processor);
        $innerConsoleHandler->reset();
        $consoleHandler->reset();

        $this->assertSame(BaseConsoleHandler::getSubscribedEvents(), ConsoleHandler::getSubscribedEvents());
    }

    /**
     * @dataProvider outputData
     *
     * @param LogLevel::* $stdErrThreshold
     * @param LogLevel::* $level
     */
    public function testCorrectOutputIsUsed(string $expectedOutput, string $silentOutput, string $stdErrThreshold, string $level): void
    {
        $errorOutput = $this->createMock(OutputInterface::class);
        $errorOutput->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_DEBUG);

        $standardOutput = $this->createMock(ConsoleOutputInterface::class);
        $standardOutput->method('getErrorOutput')->willReturn($errorOutput);
        $standardOutput->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_DEBUG);

        $event = new ConsoleCommandEvent(null, $this->createMock(InputInterface::class), $standardOutput);

        $consoleHandler = new ConsoleHandler(new BaseConsoleHandler());
        $consoleHandler->onCommand($event);

        ${$expectedOutput}->expects($this->once())
            ->method('write')
            ->with($this->stringContains('Lorem ipsum'), false, OutputInterface::VERBOSITY_DEBUG)
        ;

        ${$silentOutput}->expects($this->never())
            ->method('write')
        ;

        $consoleHandler->setStdErrThreshold(Logger::toMonologLevel($stdErrThreshold));

        $consoleHandler->handle($this->getRecord($level));
    }

    public function outputData(): iterable
    {
        yield 'NOTICE with threshold level WARNING' => [
            'standardOutput',
            'errorOutput',
            LogLevel::WARNING,
            LogLevel::NOTICE,
        ];
        yield 'WARNING with threshold level WARNING' => [
            'errorOutput',
            'standardOutput',
            LogLevel::WARNING,
            LogLevel::WARNING,
        ];
        yield 'ERROR with threshold level WARNING' => [
            'errorOutput',
            'standardOutput',
            LogLevel::WARNING,
            LogLevel::WARNING,
        ];
        yield 'WARNING with threshold level ERROR' => [
            'standardOutput',
            'errorOutput',
            LogLevel::ERROR,
            LogLevel::WARNING,
        ];
        yield 'CRITICAL with threshold level ERROR' => [
            'errorOutput',
            'standardOutput',
            LogLevel::ERROR,
            LogLevel::CRITICAL,
        ];
    }

    /**
     * @param LogLevel::* $level
     */
    private function getRecord(string $level = LogLevel::WARNING): array|LogRecord
    {
        $record = [
            'datetime' => new \DateTimeImmutable('2021-01-31 13:50:02'),
            'channel' => 'foo',
            'message' => 'Lorem ipsum',
            'level_name' => $level,
            'context' => [],
            'extra' => [],
        ];

        $record['level'] = Logger::toMonologLevel($record['level_name']);

        if (class_exists(LogRecord::class)) {
            unset($record['level_name']);
            $record = new LogRecord(...$record);
        }

        return $record;
    }
}

interface DummyResettableProcessor extends ProcessorInterface, ResettableInterface
{
}
