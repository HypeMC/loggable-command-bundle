<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

if (class_exists(LogRecord::class)) {
    trait CompatibilityFormatterTrait
    {
        public function format(LogRecord $record)
        {
            return null;
        }
    }
} else {
    trait CompatibilityFormatterTrait
    {
        public function format(array $record)
        {
            return null;
        }
    }
}

final class DummyFormatter implements FormatterInterface
{
    use CompatibilityFormatterTrait;

    public function formatBatch(array $records)
    {
        return null;
    }
}
