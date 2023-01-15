<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\DependencyInjection\Configurator;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\HandlerFactory\HandlerFactoryInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\PathResolver\PathResolverInterface;
use Monolog\Logger;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @internal
 */
final class LoggableOutputConfigurator
{
    /**
     * @var ConfigurationProviderInterface
     */
    private $configurationProvider;

    /**
     * @var PathResolverInterface
     */
    private $pathResolver;

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
        PathResolverInterface $pathResolver,
        Logger $templateLogger,
        ServiceProviderInterface $handlerFactoryLocator
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->pathResolver = $pathResolver;
        $this->templateLogger = $templateLogger;
        $this->handlerFactoryLocator = $handlerFactoryLocator;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): void
    {
        $handlerOptions = ($this->configurationProvider)($loggableOutput);

        $handlerFactory = $this->getHandlerFactory($handlerOptions['type']);

        $handlerOptions['path'] = ($this->pathResolver)($handlerOptions, $loggableOutput);

        /*
         * We clone the logger to ensure that each command gets its unique stream handler,
         * e.g. when a command is called from another command, without cloning, both would get the same logger
         * with two stream handlers, one for each command.
         */
        $logger = clone $this->templateLogger;

        $logger->pushHandler($handlerFactory($handlerOptions));

        $loggableOutput->setOutputLogger($logger);
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
