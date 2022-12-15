<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\PathResolver;

use Bizkit\LoggableCommandBundle\FilenameProvider\FilenameProviderInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class DefaultPathResolver implements PathResolverInterface
{
    /**
     * @var FilenameProviderInterface
     */
    private $filenameProvider;

    public function __construct(FilenameProviderInterface $filenameProvider)
    {
        $this->filenameProvider = $filenameProvider;
    }

    public function __invoke(array $handlerOptions, LoggableOutputInterface $loggableOutput): string
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
}
