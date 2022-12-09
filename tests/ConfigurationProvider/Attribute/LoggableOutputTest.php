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
    /**
     * @dataProvider validLevels
     */
    public function testLevelIsOKWhenStringOrInt($expected, array $arguments): void
    {
        $this->assertSame($expected, (new LoggableOutput(...$arguments))->getOptions()['level']);
    }

    public function validLevels(): iterable
    {
        yield 'Int' => [
            400,
            [null, null, null, 400],
        ];
        yield 'String' => [
            'debug',
            [null, null, null, 'debug'],
        ];
    }

    public function testExceptionIsThrownWhenLevelIsNotStringOrInt(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Level must be a string or an integer');

        new LoggableOutput(null, null, null, true);
    }

    public function testNullValuesAreFiltered(): void
    {
        $loggableOutput = new LoggableOutput('name', null, 'stream', null, true, null, 0775);

        $this->assertSame([
            'filename' => 'name',
            'type' => 'stream',
            'bubble' => true,
            'file_permission' => 0775,
        ], $loggableOutput->getOptions());
    }
}
