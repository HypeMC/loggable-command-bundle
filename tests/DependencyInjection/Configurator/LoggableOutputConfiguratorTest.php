<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Configurator;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\DefaultConfigurationProvider;
use Bizkit\LoggableCommandBundle\DependencyInjection\Configurator\LoggableOutputConfigurator;
use Bizkit\LoggableCommandBundle\PathResolver\PathResolverInterface;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyHandler;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyHandlerFactory;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @covers \Bizkit\LoggableCommandBundle\DependencyInjection\Configurator\LoggableOutputConfigurator
 */
final class LoggableOutputConfiguratorTest extends TestCase
{
    public function testLoggerIsConfiguredAsExpected(): void
    {
        $handlerOptions = [
            'type' => 'dummy',
            'path' => 'log/console/{filename}.log',
        ];
        $resolvedPath = 'log/console/dummy_loggable_output.log';

        $loggableOutput = new DummyLoggableOutput();

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('__invoke')
            ->with($loggableOutput)
            ->willReturn($handlerOptions)
        ;

        $pathResolver = $this->createMock(PathResolverInterface::class);
        $pathResolver
            ->expects($this->once())
            ->method('__invoke')
            ->with($handlerOptions, $loggableOutput)
            ->willReturn($resolvedPath)
        ;

        $configurator = new LoggableOutputConfigurator(
            $configurationProvider,
            $pathResolver,
            $templateLogger = new Logger('foo'),
            new ServiceLocator([
                'dummy' => static function () {
                    return new DummyHandlerFactory();
                },
            ])
        );

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

    public function testExceptionIsThrownIfHandlerFactoryIsNotRegistered(): void
    {
        $handlerOptions = [
            'type' => 'dummy',
        ];

        $pathResolver = $this->createMock(PathResolverInterface::class);
        $pathResolver->expects($this->never())->method('__invoke');

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
            $pathResolver,
            $templateLogger,
            new ServiceLocator([])
        );

        $loggableOutput = new DummyLoggableOutput();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The handler factory "dummy" is not registered in the handler factory locator.');

        $configurator($loggableOutput);
    }
}
