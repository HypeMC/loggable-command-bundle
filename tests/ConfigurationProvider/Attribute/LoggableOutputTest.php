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
    public function testOptionsArrayHasPrecedenceOverArguments(): void
    {
        $options = (new LoggableOutput([
            'filename' => 'yes',
            'bubble' => false,
        ], 'no', null, null, null, true))
            ->getOptions()
        ;

        $this->assertSame('yes', $options['filename']);
        $this->assertFalse($options['bubble']);
    }

    /**
     * @dataProvider validLevels
     */
    public function testLevelIsOKWhenStringOrInt($expected, array $arguments): void
    {
        $this->assertSame($expected, (new LoggableOutput(...$arguments))->getOptions()['level']);
    }

    public function validLevels(): iterable
    {
        yield 'Option int' => [
            400,
            [['level' => 400]],
        ];
        yield 'Argument string' => [
            'debug',
            [[], null, null, null, 'debug'],
        ];
    }

    /**
     * @dataProvider invalidLevels
     */
    public function testExceptionIsThrownWhenLevelIsNotStringOrInt(array $arguments): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Level must be a string or an integer');

        new LoggableOutput(...$arguments);
    }

    public function invalidLevels(): iterable
    {
        yield 'Option' => [
            [['level' => true]],
        ];
        yield 'Argument' => [
            [[], null, null, null, true],
        ];
    }

    public function testNullValuesAreFiltered(): void
    {
        $loggableOutput = new LoggableOutput([], 'name', null, 'stream', null, true, null, 0775);

        $this->assertSame([
            'filename' => 'name',
            'type' => 'stream',
            'bubble' => true,
            'file_permission' => 0775,
        ], $loggableOutput->getOptions());
    }
}
