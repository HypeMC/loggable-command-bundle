<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\HandlerFactory;

use Bizkit\LoggableCommandBundle\HandlerFactory\AbstractHandlerFactory;
use Bizkit\LoggableCommandBundle\HandlerFactory\HandlerFactoryInterface;
use Bizkit\LoggableCommandBundle\Tests\HandlerFactory\Fixtures\DummyHandlerInterface;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\ProcessorInterface;

/**
 * @covers \Bizkit\LoggableCommandBundle\HandlerFactory\AbstractHandlerFactory
 */
final class AbstractHandlerFactoryTest extends TestCase
{
    public function testProcessorAndFormatterAreNotSetWhenNull(): void
    {
        $handler = $this->createMock(AbstractProcessingHandler::class);
        $handler->expects($this->never())->method('pushProcessor');
        $handler->expects($this->never())->method('setFormatter');

        $handlerFactory = $this->createHandlerFactory($handler, null, null);
        $handlerFactory(['include_stacktraces' => false]);
    }

    public function testProcessorAndFormatterAreNotSetWhenNotSupported(): void
    {
        if (
            method_exists(HandlerInterface::class, 'pushProcessor')
            || method_exists(HandlerInterface::class, 'setFormatter')
        ) {
            $this->markTestSkipped('Monolog >= 2.0 is required.');
        }

        $processor = $this->createMock(ProcessorInterface::class);
        $formatter = $this->createMock(FormatterInterface::class);

        $handler = $this->createMock(DummyHandlerInterface::class);
        $handler->expects($this->never())->method('pushProcessor');
        $handler->expects($this->never())->method('setFormatter');

        $handlerFactory = $this->createHandlerFactory($handler, $processor, $formatter);
        $handlerFactory(['include_stacktraces' => false]);
    }

    public function testProcessorAndFormatterAreSetWhenNotNullAndAreSupported(): void
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $formatter = $this->createMock(FormatterInterface::class);

        $handler = $this->createMock(AbstractProcessingHandler::class);
        $handler->expects($this->once())->method('pushProcessor')->with($processor);
        $handler->expects($this->once())->method('setFormatter')->with($formatter);

        $handlerFactory = $this->createHandlerFactory($handler, $processor, $formatter);
        $handlerFactory(['include_stacktraces' => false]);
    }

    public function testStackTracesAreIncludedWhenTrue(): void
    {
        $lineFormatter = $this->createMock(LineFormatter::class);
        $lineFormatter->expects($this->once())->method('includeStacktraces');

        $handler = $this->createMock(AbstractProcessingHandler::class);
        $handler->expects($this->once())->method('getFormatter')->willReturn($lineFormatter);

        $handlerFactory = $this->createHandlerFactory($handler, null, $lineFormatter);
        $handlerFactory(['include_stacktraces' => true]);
    }

    public function testStackTracesAreNotIncludedWhenFalse(): void
    {
        $lineFormatter = $this->createMock(LineFormatter::class);
        $lineFormatter->expects($this->never())->method('includeStacktraces');

        $handler = $this->createMock(AbstractProcessingHandler::class);
        $handler->expects($this->never())->method('getFormatter')->willReturn($lineFormatter);

        $handlerFactory = $this->createHandlerFactory($handler, null, $lineFormatter);
        $handlerFactory(['include_stacktraces' => false]);
    }

    private function createHandlerFactory(
        HandlerInterface $handler,
        ?ProcessorInterface $psrLogMessageProcessor,
        ?FormatterInterface $formatter,
    ): HandlerFactoryInterface {
        return new class($handler, $psrLogMessageProcessor, $formatter) extends AbstractHandlerFactory {
            public function __construct(
                private readonly HandlerInterface $handler,
                ?ProcessorInterface $psrLogMessageProcessor,
                ?FormatterInterface $formatter,
            ) {
                parent::__construct($psrLogMessageProcessor, $formatter);
            }

            protected function getHandler(array $handlerOptions): HandlerInterface
            {
                return $this->handler;
            }
        };
    }
}
