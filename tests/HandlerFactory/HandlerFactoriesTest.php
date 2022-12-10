<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\HandlerFactory;

use Bizkit\LoggableCommandBundle\HandlerFactory\RotatingFileHandlerFactory;
use Bizkit\LoggableCommandBundle\HandlerFactory\StreamHandlerFactory;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LogLevel;

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
            'level' => $level = Logger::toMonologLevel(LogLevel::EMERGENCY),
            'bubble' => $bubble = false,
            'file_permission' => $filePermission = 666,
            'use_locking' => $useLocking = true,
            'filename_format' => $filenameFormat = '{filename}.{date}',
            'date_format' => $dateFormat = 'Y_m_d',
        ]);

        self::assertSame($path, $this->getPropertyValue($handler, 'filename'));
        self::assertSame($maxFiles, $this->getPropertyValue($handler, 'maxFiles'));
        self::assertSame($level, $this->getPropertyValue($handler, 'level'));
        self::assertSame($bubble, $this->getPropertyValue($handler, 'bubble'));
        self::assertSame($filePermission, $this->getPropertyValue($handler, 'filePermission'));
        self::assertSame($useLocking, $this->getPropertyValue($handler, 'useLocking'));
        self::assertSame($filenameFormat, $this->getPropertyValue($handler, 'filenameFormat'));
        self::assertSame($dateFormat, $this->getPropertyValue($handler, 'dateFormat'));
    }

    public function testStreamHandlerIsConfiguredAsExpected(): void
    {
        $handlerFactory = new StreamHandlerFactory();

        $handler = $handlerFactory([
            'include_stacktraces' => false,
            'path' => $path = __DIR__.'/foo',
            'level' => $level = Logger::toMonologLevel(LogLevel::EMERGENCY),
            'bubble' => $bubble = false,
            'file_permission' => $filePermission = 666,
            'use_locking' => $useLocking = true,
        ]);

        self::assertSame($path, $this->getPropertyValue($handler, 'url'));
        self::assertSame($level, $this->getPropertyValue($handler, 'level'));
        self::assertSame($bubble, $this->getPropertyValue($handler, 'bubble'));
        self::assertSame($filePermission, $this->getPropertyValue($handler, 'filePermission'));
        self::assertSame($useLocking, $this->getPropertyValue($handler, 'useLocking'));
    }

    private function getPropertyValue(HandlerInterface $handler, string $propertyName)
    {
        return (new \ReflectionProperty($handler, $propertyName))->getValue($handler);
    }
}
