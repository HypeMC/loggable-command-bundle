<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\HandlerFactory;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;

final class StreamHandlerFactory extends AbstractHandlerFactory
{
    protected function getHandler(array $handlerOptions): HandlerInterface
    {
        return new StreamHandler(
            $handlerOptions['path'],
            $handlerOptions['level'],
            $handlerOptions['bubble'],
            $handlerOptions['file_permission'],
            $handlerOptions['use_locking']
        );
    }
}
