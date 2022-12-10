<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Attribute;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;

/**
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput
 */
final class LoggableOutputTest extends TestCase
{
    public function testNullValuesAreFiltered(): void
    {
        $loggableOutput = new LoggableOutput('name', null, 'stream', null, true, null, 0775);

        $this->assertSame([
            'filename' => 'name',
            'type' => 'stream',
            'bubble' => true,
            'file_permission' => 0775,
        ], $loggableOutput->options);
    }
}
