<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\DefaultConfigurationProvider;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;

/**
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\DefaultConfigurationProvider
 */
final class DefaultConfigurationProviderTest extends TestCase
{
    public function testProviderReturnsDefaultConfig(): void
    {
        $handlerOptions = ['level' => 'WARNING', 'bubble' => true];

        $provider = $this->createConfigurationProvider($handlerOptions);

        $this->assertSame($handlerOptions, $provider(new DummyLoggableOutput()));
    }

    private function createConfigurationProvider(array $handlerOptions): ConfigurationProviderInterface
    {
        return new DefaultConfigurationProvider($handlerOptions);
    }
}
