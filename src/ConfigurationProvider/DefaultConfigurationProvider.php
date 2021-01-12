<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class DefaultConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var array
     */
    private $handlerOptions;

    public function __construct(array $handlerOptions)
    {
        $this->handlerOptions = $handlerOptions;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        return $this->handlerOptions;
    }
}
