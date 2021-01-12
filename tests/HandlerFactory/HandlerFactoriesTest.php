<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\HandlerFactory;

use Bizkit\LoggableCommandBundle\HandlerFactory\RotatingFileHandlerFactory;
use Bizkit\LoggableCommandBundle\HandlerFactory\StreamHandlerFactory;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

/**
 * @covers \Bizkit\LoggableCommandBundle\HandlerFactory\RotatingFileHandlerFactory
 * @covers \Bizkit\LoggableCommandBundle\HandlerFactory\StreamHandlerFactory
 */
final class HandlerFactoriesTest extends TestCase
{
    public function testRotatingFileHandlerIsConfiguredAsExpected(): void
    {
        $handlerFactory = new RotatingFileHandlerFactory();

        $handler = $handlerFactory([
            'include_stacktraces' => false,
            'path' => $path = __DIR__.'/foo',
            'max_files' => $maxFiles = 3,
            'level' => $level = Logger::EMERGENCY,
            'bubble' => $bubble = false,
            'file_permission' => $filePermission = 666,
            'use_locking' => $useLocking = true,
            'filename_format' => $filenameFormat = '{filename}.{date}',
            'date_format' => $dateFormat = 'Y_m_d',
        ]);

        $this->assertSame($path, $this->getPropertyValue($handler, 'filename'));
        $this->assertSame($maxFiles, $this->getPropertyValue($handler, 'maxFiles'));
        $this->assertSame($level, $this->getPropertyValue($handler, 'level'));
        $this->assertSame($bubble, $this->getPropertyValue($handler, 'bubble'));
        $this->assertSame($filePermission, $this->getPropertyValue($handler, 'filePermission'));
        $this->assertSame($useLocking, $this->getPropertyValue($handler, 'useLocking'));
        $this->assertSame($filenameFormat, $this->getPropertyValue($handler, 'filenameFormat'));
        $this->assertSame($dateFormat, $this->getPropertyValue($handler, 'dateFormat'));
    }

    public function testStreamHandlerIsConfiguredAsExpected(): void
    {
        $handlerFactory = new StreamHandlerFactory();

        $handler = $handlerFactory([
            'include_stacktraces' => false,
            'path' => $path = __DIR__.'/foo',
            'level' => $level = Logger::EMERGENCY,
            'bubble' => $bubble = false,
            'file_permission' => $filePermission = 666,
            'use_locking' => $useLocking = true,
        ]);

        $this->assertSame($path, $this->getPropertyValue($handler, 'url'));
        $this->assertSame($level, $this->getPropertyValue($handler, 'level'));
        $this->assertSame($bubble, $this->getPropertyValue($handler, 'bubble'));
        $this->assertSame($filePermission, $this->getPropertyValue($handler, 'filePermission'));
        $this->assertSame($useLocking, $this->getPropertyValue($handler, 'useLocking'));
    }

    private function getPropertyValue(HandlerInterface $handler, string $propertyName)
    {
        $reflectionProperty = new \ReflectionProperty($handler, $propertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($handler);
    }
}
