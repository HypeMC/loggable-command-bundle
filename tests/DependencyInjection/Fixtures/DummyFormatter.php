<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures;

use Monolog\Formatter\FormatterInterface;

final class DummyFormatter implements FormatterInterface
{
    public function format(array $record)
    {
        return null;
    }

    public function formatBatch(array $records)
    {
        return null;
    }
}
