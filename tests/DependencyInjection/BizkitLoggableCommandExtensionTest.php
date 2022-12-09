<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AttributeConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\DefaultConfigurationProvider;
use Bizkit\LoggableCommandBundle\DependencyInjection\BizkitLoggableCommandExtension;
use Bizkit\LoggableCommandBundle\DependencyInjection\Configurator\LoggableOutputConfigurator;
use Bizkit\LoggableCommandBundle\Handler\ConsoleHandler;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyConfigurationProvider;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyFormatter;
use Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures\DummyHandlerFactory;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LogLevel;
use Symfony\Bundle\MonologBundle\DependencyInjection\MonologExtension;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Bizkit\LoggableCommandBundle\DependencyInjection\BizkitLoggableCommandExtension
 */
final class BizkitLoggableCommandExtensionTest extends TestCase
{
    public function testExceptionIsThrowIfMonologBundleIsNotRegistered(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The MonologBundle extension is not registered in your application.');

        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->prepend($container);
    }

    public function testMonologConfigurationIsPrepended(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new MonologExtension());

        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());
        $container->loadFromExtension(
            $loggableCommandExtensionAlias = $loggableCommandExtension->getAlias(),
            ['channel_name' => 'foo_channel']
        );

        $loggableCommandExtension->prepend($container);

        $monologConfig = $container->getExtensionConfig('monolog');

        self::assertCount(1, $monologConfig);
        self::assertSame([
            'channels' => ['foo_channel'],
            'handlers' => [
                $loggableCommandExtensionAlias => [
                    'type' => 'service',
                    'id' => ConsoleHandler::class,
                    'channels' => ['foo_channel'],
                ],
            ],
        ], $monologConfig[0]);
    }

    /**
     * @requires PHP < 8.0
     */
    public function testAttributeConfigurationProviderIsRemovedBeforePHP8(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([], $container);

        self::assertFalse($container->hasDefinition(AttributeConfigurationProvider::class));
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testAttributeConfigurationProviderIsNotRemovedWhenPHP8(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([], $container);

        self::assertTrue($container->hasDefinition(AttributeConfigurationProvider::class));
    }

    public function testChannelNameParameterIsSet(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([[
            'channel_name' => 'foo_channel',
        ]], $container);

        self::assertTrue($container->hasParameter('bizkit_loggable_command.channel_name'));
        self::assertSame('foo_channel', $container->getParameter('bizkit_loggable_command.channel_name'));
    }

    public function testArgumentsAreReplacedAsExpected(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([[
            'channel_name' => 'foo_channel',
            'console_handler_options' => [
                'bubble' => false,
                'stderr_threshold' => 'CRITICAL',
                'verbosity_levels' => [
                    'VERBOSITY_NORMAL' => 'INFO',
                    'VERBOSITY_VERBOSE' => 'INFO',
                ],
            ],
        ]], $container);

        $definition = $container->getDefinition(ConsoleHandler::class);

        self::assertTrue($definition->hasMethodCall('setStdErrThreshold'));
        $methodCalls = array_column($definition->getMethodCalls(), 1, 0);
        self::assertSame([Logger::toMonologLevel(LogLevel::CRITICAL)], $methodCalls['setStdErrThreshold']);

        $definition = $container->getDefinition((string) $definition->getArgument(0));

        self::assertFalse($definition->getArgument(1));

        $expectedVerbosityLevels = array_map(static function (string $level): int {
            /** @var int|Level $level */
            $level = Logger::toMonologLevel($level);

            return $level instanceof Level ? $level->value : $level;
        }, [
            OutputInterface::VERBOSITY_NORMAL => LogLevel::INFO,
            OutputInterface::VERBOSITY_VERBOSE => LogLevel::INFO,
            OutputInterface::VERBOSITY_QUIET => LogLevel::ERROR,
            OutputInterface::VERBOSITY_VERY_VERBOSE => LogLevel::INFO,
            OutputInterface::VERBOSITY_DEBUG => LogLevel::DEBUG,
        ]);
        self::assertSame($expectedVerbosityLevels, $definition->getArgument(2));

        self::assertSame([
            'format' => "[%%datetime%%] %%start_tag%%%%level_name%%%%end_tag%% %%message%%\n",
        ], $definition->getArgument(3));

        $argument = $container->getDefinition(DefaultConfigurationProvider::class)->getArgument(0);
        self::assertIsArray($argument);
        self::assertArrayHasKey('path', $argument);
        self::assertArrayHasKey('level', $argument);
        self::assertArrayHasKey('bubble', $argument);

        $argument = $container->getDefinition(LoggableOutputConfigurator::class)->getArgument(2);
        self::assertInstanceOf(Reference::class, $argument);
        self::assertSame('monolog.logger.foo_channel', (string) $argument);
    }

    public function testConsoleHandlerCanBeInstantiated(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.logs_dir', '/var/log');
        $container->registerExtension(new MonologExtension());
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([[
            'channel_name' => 'foo_channel',
            'console_handler_options' => [
                'bubble' => false,
                'stderr_threshold' => 'CRITICAL',
                'verbosity_levels' => [
                    'VERBOSITY_NORMAL' => 'INFO',
                    'VERBOSITY_VERBOSE' => 'INFO',
                ],
            ],
        ]], $container);

        $container->getDefinition(ConsoleHandler::class)->setPublic(true);
        $container->compile();

        self::assertInstanceOf(ConsoleHandler::class, $container->get(ConsoleHandler::class));
    }

    public function testAutoconfigurationIsRegistered(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([], $container);

        $autoconfiguredInstanceofServices = [
            DummyConfigurationProvider::class => 'bizkit_loggable_command.configuration_provider',
            DummyHandlerFactory::class => 'bizkit_loggable_command.handler_factory',
            DummyLoggableOutput::class => 'bizkit_loggable_command.loggable_output',
        ];

        foreach ($autoconfiguredInstanceofServices as $autoconfiguredInstanceofService => $tag) {
            $container->register($autoconfiguredInstanceofService, $autoconfiguredInstanceofService)
                ->setAutoconfigured(true)
            ;
        }

        (new ResolveInstanceofConditionalsPass())->process($container);

        foreach ($autoconfiguredInstanceofServices as $autoconfiguredInstanceofService => $tag) {
            self::assertTrue($container->hasDefinition($autoconfiguredInstanceofService));
            self::assertTrue($container->getDefinition($autoconfiguredInstanceofService)->hasTag($tag));
        }
    }

    public function testFormatterAliasesAreNotRegisteredWhenFormattersAreNotConfigured(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $container->register(DummyFormatter::class);

        $loggableCommandExtension->load([], $container);

        self::assertFalse($container->hasAlias('bizkit_loggable_command.formatter.console'));
        self::assertFalse($container->hasAlias('bizkit_loggable_command.formatter.file'));
    }

    public function testFormatterAliasesAreRegisteredWhenFormattersAreConfigured(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $container->register(DummyFormatter::class);

        $loggableCommandExtension->load([[
            'console_handler_options' => [
                'formatter' => DummyFormatter::class,
            ],
            'file_handler_options' => [
                'formatter' => DummyFormatter::class,
            ],
        ]], $container);

        self::assertTrue($container->hasAlias('bizkit_loggable_command.formatter.console'));
        self::assertTrue($container->hasAlias('bizkit_loggable_command.formatter.file'));
    }

    public function testConfiguratorIsSetToLoggableOutputServices(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $container->register(DummyLoggableOutput::class)->addTag('bizkit_loggable_command.loggable_output');

        $loggableCommandExtension->load([], $container);
        $loggableCommandExtension->process($container);

        $configurator = $container->getDefinition(DummyLoggableOutput::class)->getConfigurator();
        self::assertIsArray($configurator);
        self::assertArrayHasKey(0, $configurator);
        self::assertSame(LoggableOutputConfigurator::class, (string) $configurator[0]);
    }

    public function testPsrLogMessageProcessorIsNotRegisteredWhenFalse(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([[
            'process_psr_3_messages' => false,
        ]], $container);
        $loggableCommandExtension->process($container);

        self::assertFalse($container->hasDefinition('bizkit_loggable_command.processor.psr_log_message'));
    }

    public function testPsrLogMessageProcessorIsRegisteredWhenTrueAndMonologServiceDoesNotExist(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([[
            'process_psr_3_messages' => true,
        ]], $container);
        $loggableCommandExtension->process($container);

        self::assertTrue($container->hasDefinition('bizkit_loggable_command.processor.psr_log_message'));
        self::assertFalse($container->hasAlias('bizkit_loggable_command.processor.psr_log_message'));
    }

    public function testPsrLogMessageProcessorIsAliasedWhenTrueAndMonologServiceExists(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $container->register('monolog.processor.psr_log_message', PsrLogMessageProcessor::class);

        $loggableCommandExtension->load([[
            'process_psr_3_messages' => true,
        ]], $container);
        $loggableCommandExtension->process($container);

        self::assertFalse($container->hasDefinition('bizkit_loggable_command.processor.psr_log_message'));
        self::assertTrue($container->hasAlias('bizkit_loggable_command.processor.psr_log_message'));
    }

    public function testExceptionIsThrownWhenPsrLogMessageProcessorDoesNotHaveConstructorArguments(): void
    {
        if ($this->psrLogMessageProcessorHasConstructorArguments()) {
            self::markTestSkipped('Monolog < 1.26 is needed.');
        }

        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $loggableCommandExtension->load([[
            'process_psr_3_messages' => [
                'date_format' => 'Y-m-d',
            ],
        ]], $container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Monolog 1.26 or higher is required for the "date_format" and "remove_used_context_fields" options to be used.'
        );

        $loggableCommandExtension->process($container);
    }

    public function testPsrLogMessageProcessorIsRegisteredWhenTrueWithArgumentsAndMonologServiceDoesNotExist(): void
    {
        if (!$this->psrLogMessageProcessorHasConstructorArguments()) {
            self::markTestSkipped('Monolog >= 1.26 is needed.');
        }

        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $container->register(
            'monolog.processor.psr_log_message.'.ContainerBuilder::hash([null, true]),
            PsrLogMessageProcessor::class
        );

        $loggableCommandExtension->load([[
            'process_psr_3_messages' => [
                'enabled' => true,
                'date_format' => 'Y-m-d',
            ],
        ]], $container);
        $loggableCommandExtension->process($container);

        self::assertTrue($container->hasDefinition('bizkit_loggable_command.processor.psr_log_message'));
        self::assertFalse($container->hasAlias('bizkit_loggable_command.processor.psr_log_message'));
    }

    public function testPsrLogMessageProcessorIsAliasedWhenTrueWithArgumentsAndMonologServiceExists(): void
    {
        if (!$this->psrLogMessageProcessorHasConstructorArguments()) {
            self::markTestSkipped('Monolog >= 1.26 is needed.');
        }

        $container = new ContainerBuilder();
        $container->registerExtension($loggableCommandExtension = new BizkitLoggableCommandExtension());

        $container->register(
            'monolog.processor.psr_log_message.'.ContainerBuilder::hash([null, true]),
            PsrLogMessageProcessor::class
        );

        $loggableCommandExtension->load([[
            'process_psr_3_messages' => [
                'enabled' => true,
                'remove_used_context_fields' => true,
            ],
        ]], $container);
        $loggableCommandExtension->process($container);

        self::assertFalse($container->hasDefinition('bizkit_loggable_command.processor.psr_log_message'));
        self::assertTrue($container->hasAlias('bizkit_loggable_command.processor.psr_log_message'));
    }

    private function psrLogMessageProcessorHasConstructorArguments(): bool
    {
        static $hasConstructorArguments;

        if (!isset($hasConstructorArguments)) {
            $r = (new \ReflectionClass(PsrLogMessageProcessor::class))->getConstructor();
            $hasConstructorArguments = null !== $r && $r->getNumberOfParameters() > 0;
            unset($r);
        }

        return $hasConstructorArguments;
    }
}
