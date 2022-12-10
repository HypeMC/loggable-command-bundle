<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AbstractConfigurationProvider;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;

/**
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\AbstractConfigurationProvider
 */
final class AbstractConfigurationProviderTest extends TestCase
{
    /**
     * @dataProvider configurationsToMerge
     */
    public function testConfigurationsAreMergedAsExpected(array $config1, array $config2, array $mergedConfig): void
    {
        $configurationProvider = new class($config1, $config2) extends AbstractConfigurationProvider {
            public function __construct(
                private readonly array $config1,
                private readonly array $config2,
            ) {
            }

            public function __invoke(LoggableOutputInterface $loggableOutput): array
            {
                return self::mergeConfigurations($this->config1, $this->config2);
            }
        };

        $this->assertSame($mergedConfig, $configurationProvider(new DummyLoggableOutput()));
    }

    public function configurationsToMerge(): iterable
    {
        yield 'Extra options in none' => [
            ['opt1' => 'opt1val', 'opt2' => 'opt2val'],
            ['opt1' => 'opt1otherVal', 'opt3' => 'opt3otherVal'],
            ['opt1' => 'opt1val', 'opt2' => 'opt2val', 'opt3' => 'opt3otherVal'],
        ];
        yield 'Extra options in first' => [
            ['opt1' => 'opt1val', 'opt2' => 'opt2val', 'extra_options' => ['extra1' => 'extra1val', 'extra2' => 'extra2val']],
            ['opt1' => 'opt1otherVal', 'opt3' => 'opt3otherVal'],
            ['opt1' => 'opt1val', 'opt2' => 'opt2val', 'opt3' => 'opt3otherVal', 'extra_options' => ['extra1' => 'extra1val', 'extra2' => 'extra2val']],
        ];
        yield 'Extra options in second' => [
            ['opt1' => 'opt1val', 'opt2' => 'opt2val'],
            ['opt1' => 'opt1otherVal', 'opt3' => 'opt3otherVal', 'extra_options' => ['extra1' => 'extra1val', 'extra2' => 'extra2val']],
            ['opt1' => 'opt1val', 'opt2' => 'opt2val', 'opt3' => 'opt3otherVal', 'extra_options' => ['extra1' => 'extra1val', 'extra2' => 'extra2val']],
        ];
        yield 'Extra options in both' => [
            ['opt1' => 'opt1val', 'opt2' => 'opt2val', 'extra_options' => ['extra1' => 'extra1val', 'extra2' => 'extra2val']],
            ['opt1' => 'opt1otherVal', 'opt3' => 'opt3otherVal', 'extra_options' => ['extra1' => 'extra1otherVal', 'extra3' => 'extra3otherVal']],
            ['opt1' => 'opt1val', 'opt2' => 'opt2val', 'opt3' => 'opt3otherVal', 'extra_options' => ['extra1' => 'extra1val', 'extra2' => 'extra2val', 'extra3' => 'extra3otherVal']],
        ];
    }
}
