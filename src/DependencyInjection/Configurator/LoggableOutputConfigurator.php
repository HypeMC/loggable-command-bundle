<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\DependencyInjection\Configurator;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\FilenameProvider\FilenameProviderInterface;
use Bizkit\LoggableCommandBundle\HandlerFactory\HandlerFactoryInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Monolog\Logger;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class LoggableOutputConfigurator
{
    /**
     * @var ConfigurationProviderInterface
     */
    private $configurationProvider;

    /**
     * @var FilenameProviderInterface
     */
    private $filenameProvider;

    /**
     * @var Logger
     */
    private $templateLogger;

    /**
     * @var ServiceProviderInterface
     */
    private $handlerFactoryLocator;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        FilenameProviderInterface $filenameProvider,
        Logger $templateLogger,
        ServiceProviderInterface $handlerFactoryLocator
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->filenameProvider = $filenameProvider;
        $this->templateLogger = $templateLogger;
        $this->handlerFactoryLocator = $handlerFactoryLocator;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): void
    {
        $handlerOptions = ($this->configurationProvider)($loggableOutput);

        $handlerOptionsFactory = $this->getHandlerFactory($handlerOptions['type']);

        $handlerOptions['path'] = $this->resolvePath($loggableOutput, $handlerOptions);

        /*
         * We clone the logger to ensure that each command gets its unique stream handler,
         * e.g. when a command is called from another command, without cloning, both would get the same logger
         * with two stream handlers, one for each command.
         */
        $logger = clone $this->templateLogger;

        $logger->pushHandler($handlerOptionsFactory($handlerOptions));

        $loggableOutput->setOutputLogger($logger);
    }

    private function resolvePath(LoggableOutputInterface $loggableOutput, array $handlerOptions): string
    {
        $resolvedPath = $handlerOptions['path'];

        if (false !== strpos($resolvedPath, '{filename}')) {
            $filename = $handlerOptions['filename'] ?? ($this->filenameProvider)($loggableOutput);
            $resolvedPath = strtr($resolvedPath, ['{filename}' => $filename]);
        }

        if (false !== strpos($resolvedPath, '{date}')) {
            $resolvedPath = strtr($resolvedPath, ['{date}' => date($handlerOptions['date_format'])]);
        }

        return $resolvedPath;
    }

    private function getHandlerFactory(string $handlerType): HandlerFactoryInterface
    {
        if (!$this->handlerFactoryLocator->has($handlerType)) {
            throw new \RuntimeException(
                sprintf('The handler factory "%s" is not registered in the handler factory locator.', $handlerType)
            );
        }

        return $this->handlerFactoryLocator->get($handlerType);
    }
}
