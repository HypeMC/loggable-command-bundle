# BizkitLoggableCommandBundle

[![Build Status](https://github.com/HypeMC/loggable-command-bundle/workflows/CI/badge.svg)](https://github.com/HypeMC/loggable-command-bundle/actions)
[![Latest Stable Version](https://poser.pugx.org/bizkit/loggable-command-bundle/v/stable)](https://packagist.org/packages/bizkit/loggable-command-bundle)
[![License](https://poser.pugx.org/bizkit/loggable-command-bundle/license)](https://packagist.org/packages/bizkit/loggable-command-bundle)
[![Code Coverage](https://codecov.io/gh/HypeMC/loggable-command-bundle/branch/main/graph/badge.svg)](https://codecov.io/gh/HypeMC/loggable-command-bundle)

Logs the output into a file by dynamically creating a dedicated [Monolog](https://github.com/Seldaek/monolog) file
handler for each command.

## Features

* @TODO

## Requirements

* [PHP 7.2](http://php.net/releases/7_2_0.php) or greater
* [Symfony 4.4](https://symfony.com/roadmap/4.4) or [Symfony 5.2](https://symfony.com/roadmap/5.2) or greater

## Installation

1. Require the bundle with [Composer](https://getcomposer.org/):

    ```sh
    composer require bizkit/loggable-command-bundle
    ```

1. Create the bundle configuration file under `config/packages/bizkit_loggable_command.yaml`. Here is a reference
   configuration file:

    ```yaml
    bizkit_loggable_command:

        # The name of the channel used by the console & file handlers.
        channel_name:         loggable_output

        # Configuration options for the console handler.
        console_handler_options:

            # The minimum level at which the output is sent to stderr instead of stdout.
            stderr_threshold:     ERROR
            bubble:               true
            verbosity_levels:
                VERBOSITY_QUIET:      ERROR
                VERBOSITY_NORMAL:     WARNING
                VERBOSITY_VERBOSE:    NOTICE
                VERBOSITY_VERY_VERBOSE: INFO
                VERBOSITY_DEBUG:      DEBUG
            console_formatter_options:
                format:               "[%%datetime%%] %%start_tag%%%%level_name%%%%end_tag%% %%message%%\n"
            formatter:            null

        # Configuration options for the file handler.
        file_handler_options:

            # The path where the log files are stored.
            # A {filename} & {date} placeholders are available which get resolved to the name of the log & current date.
            # The date format can be configured using the "date_format" option.
            path:                 '%kernel.logs_dir%/console/{filename}.log' # Example: '%kernel.logs_dir%/console/{filename}/{date}.log'

            # The name of the file handler factory to use.
            type:                 stream
            level:                DEBUG
            bubble:               true
            include_stacktraces:  false
            formatter:            null
            file_permission:      null
            use_locking:          false
            max_files:            0
            filename_format:      '{filename}-{date}'
            date_format:          Y-m-d

            # Extra options that can be used in custom handler factories.
            extra_options:

                # Examples:
                my_option1:          'some value'
                my_option2:          true

            # Enables configuring services with the use of an annotation, requires the Doctrine Annotation library.
            enable_annotations:   false

        # Configuration option used by both handlers.
        process_psr_3_messages: true
    ```

1. Enable the bundle in `config/bundles.php` by adding it to the array:

    ```php
    Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle::class => ['all' => true],
    ```

## Usage

@TODO

### Command

@TODO

```php
namespace App;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;

class MyLoggableCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputLogger->debug('Debug');
        $this->outputLogger->notice('Notice');

        // ...
    }
}
```

@TODO

```php
namespace App;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;

class MyLoggableCommand extends MyBaseCommand implements LoggableOutputInterface
{
    use LoggableOutputTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
```

### Message handler

@TODO

```php
namespace App;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Bizkit\LoggableCommandBundle\LoggableOutput\NamedLoggableOutputInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MyMessageHandler implements MessageHandlerInterface, NamedLoggableOutputInterface
{
    use LoggableOutputTrait;

    public function __invoke(MyMessage $myMessage)
    {
        $this->outputLogger->error('Error');
        $this->outputLogger->info('Info');
    }

    public function getOutputLogName(): string
    {
        return 'my_log_name';
    }
}
```

### PHP 8 attribute

@TODO

```php
namespace App;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;

#[LoggableOutput(filename: 'my_custom_name', type: 'rotating_file')]
class MyLoggableCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
```

@TODO

```php
namespace App;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

#[LoggableOutput(filename: 'my_log_name')]
class MyMessageHandler implements MessageHandlerInterface, LoggableOutputInterface
{
    use LoggableOutputTrait;

    public function __invoke(MyMessage $myMessage)
    {
        // ...
    }
}
```

### Doctrine annotations

@TODO

```sh
composer require doctrine/annotations
```

```yaml
bizkit_loggable_command:
    file_handler_options:
        enable_annotations: true
```

```php
namespace App;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;

/**
 * @LoggableOutput(filename="my_custom_name", type="rotating_file")
 */
class MyLoggableCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
```

## Handler factories

@TODO

### Custom handler factories

To implement a custom handler factory all you need to do is create a service which implements
the `HandlerFactoryInterface` interface.

```php
namespace App;

use Bizkit\LoggableCommandBundle\HandlerFactory\HandlerFactoryInterface;

class CustomHandlerFactory implements HandlerFactoryInterface
{
    public function __invoke(array $handlerOptions): HandlerInterface
    {
        // configure & return a monolog handler
    }
}
```

Use the FQCN of the service in the configuration:

```yaml
bizkit_loggable_command:
    file_handler_options:
        type: App\CustomHandlerFactory
```

If you are not using
Symfony's [autoconfigure](https://symfony.com/doc/4.4/service_container.html#the-autoconfigure-option)
feature or wish to use an alias in the configuration, tag the service with the `bizkit_loggable_command.handler_factory`
tag.

```yaml
App\CustomHandlerFactory:
    autoconfigure: false # @TODO why
    tags:
        - { name: bizkit_loggable_command.handler_factory, type: custom }

bizkit_loggable_command:
    file_handler_options:
        type: custom
```

@TODO AbstractHandlerFactory

## Versioning

This project adheres to [Semantic Versioning 2.0.0](http://semver.org/).

## Reporting issues

Use the [issue tracker](https://github.com/HypeMC/loggable-command-bundle/issues) to report any issues you might have.

## License

See the [LICENSE](LICENSE) file for license rights and limitations (MIT).
