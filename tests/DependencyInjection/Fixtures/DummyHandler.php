<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures;

use Monolog\Handler\AbstractHandler;
use Monolog\LogRecord;

if (class_exists(LogRecord::class)) {
    trait CompatibilityHandlerTrait
    {
        public function handle(LogRecord $record): bool
        {
            return true;
        }
    }
} else {
    trait CompatibilityHandlerTrait
    {
        public function handle(array $record): bool
        {
            return true;
        }
    }
}

final class DummyHandler extends AbstractHandler
{
    use CompatibilityHandlerTrait;

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
}
