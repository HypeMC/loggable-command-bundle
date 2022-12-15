<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider;

use Bizkit\LoggableCommandBundle\FilenameProvider\DefaultFilenameProvider;
use Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures\DummyLoggableCommand;
use Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures\DummyLoggableCommandWithoutName;
use Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures\DummyNamedLoggableCommand;
use Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures\DummyNamedLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;

/**
 * @covers \Bizkit\LoggableCommandBundle\FilenameProvider\DefaultFilenameProvider
 */
final class DefaultFilenameProviderTest extends TestCase
{
    public function testNameFromNamedLoggableOutput(): void
    {
        $filenameProvider = new DefaultFilenameProvider();

        $this->assertSame('some_custom_name', $filenameProvider(new DummyNamedLoggableOutput()));
    }

    public function testNameFromCommandName(): void
    {
        $filenameProvider = new DefaultFilenameProvider();

        $this->assertSame('the_command_name', $filenameProvider(new DummyLoggableCommand()));
    }

    public function testNamedLoggableOutputHasPrecedenceOverCommandName(): void
    {
        $filenameProvider = new DefaultFilenameProvider();

        $this->assertSame('this_has_precedence', $filenameProvider(new DummyNamedLoggableCommand()));
    }

    public function testFallbackNameFromClassname(): void
    {
        $filenameProvider = new DefaultFilenameProvider();

        $this->assertSame('dummy_loggable_output', $filenameProvider(new DummyLoggableOutput()));
        $this->assertSame('dummy_loggable_command_without_name', $filenameProvider(new DummyLoggableCommandWithoutName()));
    }
}
