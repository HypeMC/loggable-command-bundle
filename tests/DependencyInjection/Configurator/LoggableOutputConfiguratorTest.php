<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Configurator;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\DefaultConfigurationProvider;
use Bizkit\LoggableCommandBundle\DependencyInjection\Configurator\LoggableOutputConfigurator;
use Bizkit\LoggableCommandBundle\FilenameProvider\DefaultFilenameProvider;
use Bizkit\LoggableCommandBundle\FilenameProvider\FilenameProviderInterface;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyHandler;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyHandlerFactory;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Logger;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @covers \Bizkit\LoggableCommandBundle\DependencyInjection\Configurator\LoggableOutputConfigurator
 *
 * @group time-sensitive
 */
final class LoggableOutputConfiguratorTest extends TestCase
{
    /**
     * @dataProvider handlerOptions
     */
    public function testLoggerIsConfiguredAsExpected(array $handlerOptions, string $resolvedPath): void
    {
        ClockMock::withClockMock(1612711778);

        $configurator = new LoggableOutputConfigurator(
            new DefaultConfigurationProvider($handlerOptions),
            new DefaultFilenameProvider(),
            $templateLogger = new Logger('foo'),
            new ServiceLocator([
                'dummy' => static function () {
                    return new DummyHandlerFactory();
                },
            ])
        );

        $loggableOutput = new DummyLoggableOutput();

        $configurator($loggableOutput);

        /** @var Logger $logger */
        $logger = $loggableOutput->getOutputLogger();

        $this->assertNotSame($templateLogger, $logger);

        $this->assertCount(0, $templateLogger->getHandlers());
        $this->assertCount(1, $logger->getHandlers());

        /** @var DummyHandler $handler */
        $handler = $logger->popHandler();

        $this->assertInstanceOf(DummyHandler::class, $handler);
        $this->assertSame($resolvedPath, $handler->getHandlerOptions()['path']);
    }

    public function handlerOptions(): iterable
    {
        yield 'Filename & date' => [[
            'type' => 'dummy',
            'path' => 'log/console/{filename}-{date}.log',
            'date_format' => 'Y_m_d',
        ], 'log/console/dummy_loggable_output-2021_02_07.log'];

        yield 'Filename from provider' => [[
            'type' => 'dummy',
            'path' => 'log/console/{filename}.log',
        ], 'log/console/dummy_loggable_output.log'];

        yield 'Filename' => [[
            'type' => 'dummy',
            'path' => 'log/console/{filename}.log',
            'filename' => 'baz',
        ], 'log/console/baz.log'];

        yield 'Date' => [[
            'type' => 'dummy',
            'path' => 'log/console/foo-{date}.log',
            'date_format' => 'Y_m_d',
        ], 'log/console/foo-2021_02_07.log'];

        yield 'None' => [[
            'type' => 'dummy',
            'path' => 'log/console/foo.log',
        ], 'log/console/foo.log'];
    }

    public function testFilenameProviderIsNotCalledWhenFilenameIsProvided(): void
    {
        $handlerOptions = [
            'type' => 'dummy',
            'path' => 'log/console/{filename}.log',
            'filename' => 'dummy-filename',
        ];

        $filenameProvider = $this->createMock(FilenameProviderInterface::class);
        $filenameProvider->expects($this->never())->method('__invoke');

        $configurator = new LoggableOutputConfigurator(
            new DefaultConfigurationProvider($handlerOptions),
            $filenameProvider,
            new Logger('foo'),
            new ServiceLocator([
                'dummy' => static function () {
                    return new DummyHandlerFactory();
                },
            ])
        );

        $loggableOutput = new DummyLoggableOutput();

        $configurator($loggableOutput);

        /** @var Logger $logger */
        $logger = $loggableOutput->getOutputLogger();

        /** @var DummyHandler $handler */
        $handler = $logger->popHandler();

        $this->assertSame('log/console/dummy-filename.log', $handler->getHandlerOptions()['path']);
    }

    public function testExceptionIsThrownIfHandlerFactoryIsNotRegistered(): void
    {
        $handlerOptions = [
            'type' => 'dummy',
            'path' => 'log/console/{filename}.log',
        ];

        $filenameProvider = $this->createMock(FilenameProviderInterface::class);
        $filenameProvider->expects($this->never())->method('__invoke');

        $templateLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['pushHandler'])
            ->addMethods(['__clone'])
            ->getMock()
        ;
        $templateLogger->expects($this->never())->method('__clone');
        $templateLogger->expects($this->never())->method('pushHandler');

        $configurator = new LoggableOutputConfigurator(
            new DefaultConfigurationProvider($handlerOptions),
            $filenameProvider,
            $templateLogger,
            new ServiceLocator([])
        );

        $loggableOutput = new DummyLoggableOutput();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The handler factory "dummy" is not registered in the handler factory locator.');

        $configurator($loggableOutput);
    }
}
