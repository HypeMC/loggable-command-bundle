<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\DependencyInjection;

use Symfony\Bundle\MonologBundle\DependencyInjection\Configuration as MonologConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $monologHandlersConfig = $this->getMonologHandlersConfigurationNode();

        $treeBuilder = new TreeBuilder('bizkit_loggable_command');

        $treeBuilder
            ->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('channel_name')
                    ->info('The name of the channel used by the console & file handlers.')
                    ->cannotBeEmpty()
                    ->defaultValue('loggable_output')
                ->end()

                ->arrayNode('console_handler_options')
                    ->info('Configuration options for the console handler.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('stderr_threshold')
                            ->info('The minimum level at which the output is sent to stderr instead of stdout.')
                            ->defaultValue('ERROR')
                        ->end()
                        ->append($monologHandlersConfig->find('bubble'))
                        ->append($monologHandlersConfig->find('verbosity_levels'))
                        ->arrayNode('console_formatter_options')
                            ->addDefaultsIfNotSet()
                            ->ignoreExtraKeys(false)
                            ->children()
                                ->scalarNode('format')
                                    ->defaultValue("[%%datetime%%] %%start_tag%%%%level_name%%%%end_tag%% %%message%%\n")
                                ->end()
                            ->end()
                        ->end()
                        ->append($monologHandlersConfig->find('formatter')->defaultNull())
                    ->end()
                ->end()

                ->arrayNode('file_handler_options')
                    ->info('Configuration options for the file handler.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')
                            ->info(<<<'EOT'
The path where the log files are stored.
A {filename} & {date} placeholders are available which get resolved to the name of the log & current date.
The date format can be configured using the "date_format" option.
EOT
                            )
                            ->cannotBeEmpty()
                            ->defaultValue('%kernel.logs_dir%/console/{filename}.log')
                            ->example('%kernel.logs_dir%/console/{filename}/{date}.log')
                        ->end()
                        ->scalarNode('type')
                            ->info('The name of the file handler factory to use.')
                            ->cannotBeEmpty()
                            ->defaultValue('stream')
                        ->end()
                        ->append($monologHandlersConfig->find('level'))
                        ->append($monologHandlersConfig->find('bubble'))
                        ->append($monologHandlersConfig->find('include_stacktraces'))
                        ->append($monologHandlersConfig->find('formatter')->defaultNull())
                        ->append($monologHandlersConfig->find('file_permission'))
                        ->append($monologHandlersConfig->find('use_locking'))
                        ->append($monologHandlersConfig->find('max_files'))
                        ->append($monologHandlersConfig->find('filename_format'))
                        ->append($monologHandlersConfig->find('date_format'))
                        ->arrayNode('extra_options')
                            ->info('Extra options that can be used in custom handler factories.')
                            ->example([
                                'my_option1' => 'some value',
                                'my_option2' => true,
                            ])
                            ->addDefaultsIfNotSet()
                            ->ignoreExtraKeys(false)
                        ->end()
                        ->booleanNode('enable_annotations')
                            ->info('Enables configuring services with the use of an annotation, requires the Doctrine Annotation library.')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('process_psr_3_messages')
                    ->info('Configuration option used by both handlers.')
                    ->example([
                        false,
                        ['enabled' => false],
                        ['date_format' => 'Y-m-d', 'remove_used_context_fields' => true],
                    ])
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->ifTrue(static function ($v): bool {
                            return !\is_array($v);
                        })
                        ->then(static function ($v): array {
                            return ['enabled' => $v];
                        })
                    ->end()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('date_format')->end()
                        ->booleanNode('remove_used_context_fields')->end()
                    ->end()
                ->end()
            ->end()

            ->validate()
                ->ifTrue(static function (array $v): bool {
                    return !isset($v['console_handler_options']['verbosity_levels']);
                })
                ->then(static function (array $v): array {
                    $v['console_handler_options']['verbosity_levels'] = [];

                    return $v;
                })
            ->end()
        ;

        return $treeBuilder;
    }

    private function getMonologHandlersConfigurationNode(): ArrayNodeDefinition
    {
        return (new MonologConfiguration())->getConfigTreeBuilder()->getRootNode()->find('handlers.');
    }
}
