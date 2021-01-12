<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AttributeConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutputWithAttribute;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Logger;

/**
 * @requires PHP >= 8.0
 *
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\AttributeConfigurationProvider
 */
final class AttributeConfigurationProviderTest extends TestCase
{
    public function testProviderReturnsExpectedConfigWhenAttributeIsFound(): void
    {
        $provider = $this->createConfigurationProvider();

        $this->assertSame(
            ['filename' => 'attribute-test', 'level' => Logger::EMERGENCY, 'bubble' => true],
            $provider(new DummyLoggableOutputWithAttribute())
        );
    }

    public function testProviderReturnsEmptyConfigWhenAttributeIsNotFound(): void
    {
        $provider = $this->createConfigurationProvider();

        $this->assertSame([], $provider(new DummyLoggableOutput()));
    }

    private function createConfigurationProvider(): ConfigurationProviderInterface
    {
        return new AttributeConfigurationProvider();
    }
}
