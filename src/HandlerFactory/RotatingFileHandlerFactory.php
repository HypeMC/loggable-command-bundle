<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\HandlerFactory;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;

final class RotatingFileHandlerFactory extends AbstractHandlerFactory
{
    protected function getHandler(array $handlerOptions): HandlerInterface
    {
        $handler = new RotatingFileHandler(
            $handlerOptions['path'],
            $handlerOptions['max_files'],
            $handlerOptions['level'],
            $handlerOptions['bubble'],
            $handlerOptions['file_permission'],
            $handlerOptions['use_locking']
        );

        $handler->setFilenameFormat(
            $handlerOptions['filename_format'],
            $handlerOptions['date_format']
        );

        return $handler;
    }
}
