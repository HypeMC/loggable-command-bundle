<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\HandlerFactory;

use Monolog\Handler\HandlerInterface;

interface HandlerFactoryInterface
{
    public function __invoke(array $handlerOptions): HandlerInterface;
}
