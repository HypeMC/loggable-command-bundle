<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\Handler;

use Bizkit\LoggableCommandBundle\Handler\ConsoleHandler;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler as BaseConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
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
    public function testHandlerIsDecoratedAsExpected(): void
    {
        $innerConsoleHandler = new BaseConsoleHandler(null, true, [
            OutputInterface::VERBOSITY_NORMAL => Logger::WARNING,
        ]);
        $consoleHandler = new ConsoleHandler($innerConsoleHandler);

        $output = $this->createMock(OutputInterface::class);
        $output->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $consoleHandler->setOutput($output);

        $output->expects($this->once())->method('write');
        $consoleHandler->handleBatch([
            [
                'datetime' => new \DateTimeImmutable('2021-01-31 13:50:02'),
                'channel' => 'foo',
                'message' => 'Lorem ipsum',
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
            ],
        ]);

        $this->assertFalse($innerConsoleHandler->isHandling(['level' => Logger::DEBUG]));
        $this->assertFalse($consoleHandler->isHandling(['level' => Logger::DEBUG]));
        $this->assertTrue($innerConsoleHandler->isHandling(['level' => Logger::ERROR]));
        $this->assertTrue($consoleHandler->isHandling(['level' => Logger::ERROR]));

        $consoleHandler->setBubble(false);
        $this->assertFalse($innerConsoleHandler->getBubble());
        $this->assertFalse($consoleHandler->getBubble());
        $consoleHandler->setBubble(true);
        $this->assertTrue($innerConsoleHandler->getBubble());
        $this->assertTrue($consoleHandler->getBubble());

        $consoleHandler->setLevel(Logger::NOTICE);
        $this->assertSame(Logger::NOTICE, $innerConsoleHandler->getLevel());
        $this->assertSame(Logger::NOTICE, $consoleHandler->getLevel());
        $consoleHandler->setLevel(Logger::WARNING);
        $this->assertSame(Logger::WARNING, $innerConsoleHandler->getLevel());
        $this->assertSame(Logger::WARNING, $consoleHandler->getLevel());

        $consoleHandler->onTerminate(new ConsoleTerminateEvent(
            new Command(), $this->createMock(InputInterface::class), $this->createMock(OutputInterface::class), 0
        ));
        $this->assertFalse($innerConsoleHandler->isHandling(['level' => Logger::ERROR]));
        $this->assertFalse($consoleHandler->isHandling(['level' => Logger::ERROR]));

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

        $consoleHandler = new ConsoleHandler(new BaseConsoleHandler());
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

interface DummyResettableProcessor extends ProcessorInterface, ResettableInterface
{
}
