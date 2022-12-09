<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection;

use Bizkit\LoggableCommandBundle\DependencyInjection\Configuration;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Bizkit\LoggableCommandBundle\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), ['bizkit_loggable_command' => []]);

        $this->assertSame([
            'channel_name' => 'loggable_output',
            'console_handler_options' => [
                'stderr_threshold' => 'ERROR',
                'bubble' => true,
                'console_formatter_options' => [
                    'format' => "[%%datetime%%] %%start_tag%%%%level_name%%%%end_tag%% %%message%%\n",
                ],
                'formatter' => null,
                'verbosity_levels' => [],
            ],
            'file_handler_options' => [
                'path' => '%kernel.logs_dir%/console/{filename}.log',
                'type' => 'stream',
                'level' => 'DEBUG',
                'bubble' => true,
                'include_stacktraces' => false,
                'formatter' => null,
                'file_permission' => null,
                'use_locking' => false,
                'max_files' => 0,
                'filename_format' => '{filename}-{date}',
                'date_format' => 'Y-m-d',
                'extra_options' => [],
            ],
            'process_psr_3_messages' => [
                'enabled' => true,
            ],
        ], $config);
    }

    public function testConfigWhenProcessPsr3MessageIsTrue(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [
            'bizkit_loggable_command' => [
                'process_psr_3_messages' => true,
            ],
        ]);

        $this->assertArrayHasKey('process_psr_3_messages', $config);
        $this->assertSame([
            'enabled' => true,
        ], $config['process_psr_3_messages']);
    }

    public function testConfigWhenProcessPsr3MessageIsFalse(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [
            'bizkit_loggable_command' => [
                'process_psr_3_messages' => false,
            ],
        ]);

        $this->assertArrayHasKey('process_psr_3_messages', $config);
        $this->assertSame([
            'enabled' => false,
        ], $config['process_psr_3_messages']);
    }

    public function testConfigWhenProcessPsr3MessageIsArray(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [
            'bizkit_loggable_command' => [
                'process_psr_3_messages' => [
                    'date_format' => 'Y-m-d',
                ],
            ],
        ]);

        $this->assertArrayHasKey('process_psr_3_messages', $config);
        $this->assertSame([
            'date_format' => 'Y-m-d',
            'enabled' => true,
        ], $config['process_psr_3_messages']);
    }
}
