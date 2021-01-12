<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures;

use Bizkit\LoggableCommandBundle\HandlerFactory\HandlerFactoryInterface;
use Monolog\Handler\HandlerInterface;

final class DummyHandlerFactory implements HandlerFactoryInterface
{
    public function __invoke(array $handlerOptions): HandlerInterface
    {
        return new DummyHandler($handlerOptions);
    }
}
