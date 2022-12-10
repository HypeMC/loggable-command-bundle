<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\HandlerFactory;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\MonologBundle\MonologBundle;

abstract class AbstractHandlerFactory implements HandlerFactoryInterface
{
    public function __construct(
        private readonly ?ProcessorInterface $psrLogMessageProcessor = null,
        private readonly ?FormatterInterface $formatter = null,
    ) {
    }

    public function __invoke(array $handlerOptions): HandlerInterface
    {
        $handler = $this->getHandler($handlerOptions);

        if (
            null !== $this->psrLogMessageProcessor
            && ($handler instanceof ProcessableHandlerInterface || method_exists(HandlerInterface::class, 'pushProcessor'))
        ) {
            $handler->pushProcessor($this->psrLogMessageProcessor);
        }

        if (
            null !== $this->formatter
            && ($handler instanceof FormattableHandlerInterface || method_exists(HandlerInterface::class, 'setFormatter'))
        ) {
            $handler->setFormatter($this->formatter);
        }

        if ($handlerOptions['include_stacktraces']) {
            MonologBundle::includeStacktraces($handler);
        }

        return $handler;
    }

    abstract protected function getHandler(array $handlerOptions): HandlerInterface;
}
