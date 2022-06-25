<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\DependencyInjection;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AnnotationConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\AttributeConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\DefaultConfigurationProvider;
use Bizkit\LoggableCommandBundle\DependencyInjection\Configurator\LoggableOutputConfigurator;
use Bizkit\LoggableCommandBundle\Handler\ConsoleHandler;
use Bizkit\LoggableCommandBundle\HandlerFactory\HandlerFactoryInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Doctrine\Common\Annotations\Annotation;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class BizkitLoggableCommandExtension extends ConfigurableExtension implements PrependExtensionInterface, CompilerPassInterface
{
    /**
     * @var array{enabled: bool, date_format?: ?string, remove_used_context_fields?: bool}
     */
    private $processPsr3Messages;

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('monolog')) {
            throw new RuntimeException('The MonologBundle extension is not registered in your application.');
        }

        $configs = $container->getExtensionConfig($this->getAlias());
        $mergedConfig = $this->processConfiguration(new Configuration(), $configs);

        $monologConfig = [
            'channels' => [$mergedConfig['channel_name']],
            'handlers' => [
                $this->getAlias() => [
                    'type' => 'service',
                    'id' => ConsoleHandler::class,
                    'channels' => [$mergedConfig['channel_name']],
                ],
            ],
        ];

        $container->prependExtensionConfig('monolog', $monologConfig);
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $this->processPsr3Messages = $mergedConfig['process_psr_3_messages'];

        $loader = new Loader\XmlFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.xml');

        if (\PHP_VERSION_ID < 80000) {
            $container->removeDefinition(AttributeConfigurationProvider::class);
        }

        if ($mergedConfig['file_handler_options']['enable_annotations']) {
            if (!class_exists(Annotation::class)) {
                throw new LogicException('Annotations cannot be enabled as the Doctrine Annotation library is not installed. Try running "composer require doctrine/annotations".');
            }
        } else {
            $container->removeDefinition(AnnotationConfigurationProvider::class);
        }

        $container->setParameter('bizkit_loggable_command.channel_name', $mergedConfig['channel_name']);

        $container->registerForAutoconfiguration(ConfigurationProviderInterface::class)
            ->addTag('bizkit_loggable_command.configuration_provider')
        ;

        $container->registerForAutoconfiguration(HandlerFactoryInterface::class)
            ->addTag('bizkit_loggable_command.handler_factory')
        ;

        $container->registerForAutoconfiguration(LoggableOutputInterface::class)
            ->addTag('bizkit_loggable_command.loggable_output')
        ;

        $stdErrThreshold = Logger::toMonologLevel($mergedConfig['console_handler_options']['stderr_threshold']);
        $consoleHandler = $container
            ->getDefinition(ConsoleHandler::class)
            ->addMethodCall('setStdErrThreshold', [$stdErrThreshold])
        ;
        $container
            ->getDefinition((string) $consoleHandler->getArgument(0))
            ->replaceArgument(1, $mergedConfig['console_handler_options']['bubble'])
            ->replaceArgument(2, $mergedConfig['console_handler_options']['verbosity_levels'])
            ->replaceArgument(3, $mergedConfig['console_handler_options']['console_formatter_options'])
        ;

        if (null !== $formatter = $mergedConfig['console_handler_options']['formatter']) {
            $container->setAlias('bizkit_loggable_command.formatter.console', $formatter)
                ->setPublic(false)
            ;
        }

        if (null !== $formatter = $mergedConfig['file_handler_options']['formatter']) {
            $container->setAlias('bizkit_loggable_command.formatter.file', $formatter)
                ->setPublic(false)
            ;
        }

        $container
            ->getDefinition(DefaultConfigurationProvider::class)
            ->replaceArgument(0, $mergedConfig['file_handler_options'])
        ;

        $container
            ->getDefinition(LoggableOutputConfigurator::class)
            ->replaceArgument(2, new Reference(sprintf('monolog.logger.%s', $mergedConfig['channel_name'])))
        ;
    }

    public function process(ContainerBuilder $container): void
    {
        $this->setLoggableOutputConfigurator($container);
        $this->registerPsrLogMessageProcessor($container);
    }

    /**
     * Needs to happen after {@see ResolveInstanceofConditionalsPass}.
     *
     * We can't use {@see ContainerBuilder::registerForAutoconfiguration} with {@see Definition::setConfigurator}
     * since that wouldn't work when autoconfigure is disabled.
     */
    private function setLoggableOutputConfigurator(ContainerBuilder $container): void
    {
        $loggableOutputConfiguratorRef = new Reference(LoggableOutputConfigurator::class);

        $taggedServices = $container->findTaggedServiceIds('bizkit_loggable_command.loggable_output');

        foreach ($taggedServices as $id => $tags) {
            $container->getDefinition($id)->setConfigurator($loggableOutputConfiguratorRef);
        }
    }

    /**
     * Needs to happen after {@see MonologExtension::load}.
     */
    private function registerPsrLogMessageProcessor(ContainerBuilder $container): void
    {
        if (!$this->processPsr3Messages['enabled']) {
            return;
        }

        static $hasConstructorArguments;

        if (!isset($hasConstructorArguments)) {
            $r = (new \ReflectionClass(PsrLogMessageProcessor::class))->getConstructor();
            $hasConstructorArguments = null !== $r && $r->getNumberOfParameters() > 0;
            unset($r);
        }

        $monologProcessorId = 'monolog.processor.psr_log_message';
        $monologProcessorArguments = [];
        $processorId = 'bizkit_loggable_command.processor.psr_log_message';

        $processorOptions = $this->processPsr3Messages;
        unset($processorOptions['enabled']);

        if ($processorOptions) {
            if (!$hasConstructorArguments) {
                throw new RuntimeException('Monolog 1.26 or higher is required for the "date_format" and "remove_used_context_fields" options to be used.');
            }
            $monologProcessorArguments = [
                $processorOptions['date_format'] ?? null,
                $processorOptions['remove_used_context_fields'] ?? false,
            ];
            $monologProcessorId .= '.'.ContainerBuilder::hash($monologProcessorArguments);
        }

        if ($container->hasDefinition($monologProcessorId)) {
            $container->setAlias($processorId, $monologProcessorId)
                ->setPublic(false)
            ;
        } else {
            $processor = new Definition(PsrLogMessageProcessor::class);
            $processor->setPublic(false);
            $processor->setArguments($monologProcessorArguments);
            $container->setDefinition($processorId, $processor);
        }
    }
}
