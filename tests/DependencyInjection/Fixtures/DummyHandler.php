<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures;

use Monolog\Handler\AbstractHandler;

final class DummyHandler extends AbstractHandler
{
    /**
     * @var array
     */
    private $handlerOptions;

    public function __construct(array $handlerOptions)
    {
        parent::__construct();

        $this->handlerOptions = $handlerOptions;
    }

    public function getHandlerOptions(): array
    {
        return $this->handlerOptions;
    }

    public function handle(array $record): bool
    {
        return true;
    }
}
