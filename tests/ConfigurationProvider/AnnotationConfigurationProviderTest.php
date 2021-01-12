<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AnnotationConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutputWithAnnotation;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Monolog\Logger;

/**
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\AnnotationConfigurationProvider
 */
final class AnnotationConfigurationProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Annotation::class)) {
            $this->markTestSkipped('Doctrine Annotation library is required.');
        }
    }

    public function testProviderReturnsExpectedConfigWhenAnnotationIsFound(): void
    {
        $provider = $this->createConfigurationProvider();

        $this->assertSame(
            ['filename' => 'annotation-test', 'level' => Logger::CRITICAL, 'max_files' => 4],
            $provider(new DummyLoggableOutputWithAnnotation())
        );
    }

    public function testProviderReturnsEmptyConfigWhenAnnotationIsNotFound(): void
    {
        $provider = $this->createConfigurationProvider();

        $this->assertSame([], $provider(new DummyLoggableOutput()));
    }

    private function createConfigurationProvider(): ConfigurationProviderInterface
    {
        return new AnnotationConfigurationProvider(new AnnotationReader(new DocParser()));
    }
}
