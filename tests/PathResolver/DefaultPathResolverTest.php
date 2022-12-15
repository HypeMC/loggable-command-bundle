<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\PathResolver;

use Bizkit\LoggableCommandBundle\FilenameProvider\DefaultFilenameProvider;
use Bizkit\LoggableCommandBundle\FilenameProvider\FilenameProviderInterface;
use Bizkit\LoggableCommandBundle\PathResolver\DefaultPathResolver;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Bizkit\LoggableCommandBundle\PathResolver\DefaultPathResolver
 *
 * @group time-sensitive
 */
final class DefaultPathResolverTest extends TestCase
{
    /**
     * @dataProvider handlerOptions
     */
    public function testLoggerIsConfiguredAsExpected(array $handlerOptions, string $resolvedPath): void
    {
        ClockMock::withClockMock(1612711778);

        $pathResolver = new DefaultPathResolver(new DefaultFilenameProvider());

        $this->assertSame($resolvedPath, $pathResolver($handlerOptions, new DummyLoggableOutput()));
    }

    public function handlerOptions(): iterable
    {
        yield 'Filename & date' => [[
            'path' => 'log/console/{filename}-{date}.log',
            'date_format' => 'Y_m_d',
        ], 'log/console/dummy_loggable_output-2021_02_07.log'];

        yield 'Filename from provider' => [[
            'path' => 'log/console/{filename}.log',
        ], 'log/console/dummy_loggable_output.log'];

        yield 'Filename' => [[
            'path' => 'log/console/{filename}.log',
            'filename' => 'baz',
        ], 'log/console/baz.log'];

        yield 'Date' => [[
            'path' => 'log/console/foo-{date}.log',
            'date_format' => 'Y_m_d',
        ], 'log/console/foo-2021_02_07.log'];

        yield 'None' => [[
            'path' => 'log/console/foo.log',
        ], 'log/console/foo.log'];
    }

    public function testFilenameProviderIsNotCalledWhenFilenameIsProvided(): void
    {
        $handlerOptions = [
            'path' => 'log/console/{filename}.log',
            'filename' => 'dummy-filename',
        ];

        $filenameProvider = $this->createMock(FilenameProviderInterface::class);
        $filenameProvider->expects($this->never())->method('__invoke');

        $pathResolver = new DefaultPathResolver($filenameProvider);

        $this->assertSame('log/console/dummy-filename.log', $pathResolver($handlerOptions, new DummyLoggableOutput()));
    }
}
