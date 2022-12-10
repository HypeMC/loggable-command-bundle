<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\MergedConfigurationProvider;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Psr\Log\LogLevel;

/**
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\MergedConfigurationProvider
 */
final class MergedConfigurationProviderTest extends TestCase
{
    public function testConfigsAreMergedAsExpected(): void
    {
        $loggableOutput = new DummyLoggableOutput();

        $provider = $this->createConfigurationProvider(
            $loggableOutput,
            ['filename' => 'foo', 'level' => LogLevel::CRITICAL, 'max_files' => 4, 'extra_options' => [
                'foo' => 'one',
                'bar' => 'two',
            ]],
            ['filename' => 'bar', 'level' => LogLevel::EMERGENCY, 'bubble' => true, 'extra_options' => [
                'foo' => 'new one',
                'baz' => 'three',
            ]],
        );

        $this->assertSame(
            ['filename' => 'foo', 'level' => LogLevel::CRITICAL, 'max_files' => 4, 'bubble' => true, 'extra_options' => [
                'foo' => 'one',
                'bar' => 'two',
                'baz' => 'three',
            ]],
            $provider($loggableOutput),
        );
    }

    private function createConfigurationProvider(
        LoggableOutputInterface $loggableOutput,
        array ...$handlerOptionsGroups,
    ): ConfigurationProviderInterface {
        $handlers = [];

        foreach ($handlerOptionsGroups as $handlerOptions) {
            $handler = $this->createMock(ConfigurationProviderInterface::class);
            $handler->expects($this->once())
                ->method('__invoke')
                ->with($loggableOutput)
                ->willReturn($handlerOptions)
            ;

            $handlers[] = $handler;
        }

        return new MergedConfigurationProvider($handlers);
    }
}
