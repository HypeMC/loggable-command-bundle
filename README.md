# BizkitLoggableCommandBundle

[![Build Status](https://github.com/HypeMC/loggable-command-bundle/workflows/CI/badge.svg)](https://github.com/HypeMC/loggable-command-bundle/actions)
[![Latest Stable Version](https://poser.pugx.org/bizkit/loggable-command-bundle/v/stable)](https://packagist.org/packages/bizkit/loggable-command-bundle)
[![License](https://poser.pugx.org/bizkit/loggable-command-bundle/license)](https://packagist.org/packages/bizkit/loggable-command-bundle)
[![Code Coverage](https://codecov.io/gh/HypeMC/loggable-command-bundle/branch/main/graph/badge.svg)](https://codecov.io/gh/HypeMC/loggable-command-bundle)

Symfony bundle which creates a dedicated Monolog log file for each command or message handler.

## Features

* Dynamically creates a dedicated Monolog file handler for each command or message handler
* Uses Monolog's console handler to display the output inside a terminal
* Supports using Monolog's stream or rotating file handlers & creating custom handler factories
* Automatically excludes the configured Monolog channel from all other handlers with exclusive channels
* Allows per command configuration through the use of PHP 8 attributes or Doctrine annotations
* Supports configuring which output stream should be used by certain log levels (`stdout` or `stderr`)

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
        process_psr_3_messages:

            # Examples:
            - false
            - { enabled: false }
            - { date_format: Y-m-d, remove_used_context_fields: true }
            enabled:              true
            date_format:          ~
            remove_used_context_fields: ~
    ```

1. Enable the bundle in `config/bundles.php` by adding it to the array:

    ```php
    Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle::class => ['all' => true],
    ```

## Usage

The bundle provides a way to log the output of a [Symfony Command](https://symfony.com/doc/current/console.html)
or [Symfony Messenger's](https://symfony.com/doc/current/messenger.html#creating-a-message-handler) message handler into
a dedicated file by dynamically creating a [Monolog](https://github.com/Seldaek/monolog) file handler & logger for each
service. Supported file handlers are the `stream` & `rotating_file` handlers. To use other file handlers,
a [custom handler factory](#handler-factories) must be implemented & registered.

The output logger also uses a console handler to display the output inside a terminal. The `stderr_threshold` option can
be used to set the log level at which the output is start being sent to the `stderr` stream instead of the `stdout`.

Other Monolog handlers can be added to the output logger as well as described in
the [dedicated section](#adding-other-monolog-handlers-to-the-output-logger).

### Command

The simplest way to enable output logging in a Symfony Command is by extending the `LoggableCommand` class. The output
logger can be accessed through the `$outputLogger` property. By default, the name of the log file will be the snake
cased version of the command name, e.g. `app_my_loggable`.

```php
namespace App;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;

class MyLoggableCommand extends LoggableCommand
{
    protected static $defaultName = 'app:my-loggable';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputLogger->debug('Debug');
        $this->outputLogger->notice('Notice');

        // ...
    }
}
```

Instead of extending the `LoggableCommand` class, you can also use the `LoggableOutputTrait` with
the `LoggableOutputInterface`. This is useful when you have a custom base command class.

```php
namespace App;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;

class MyLoggableCommand extends MyBaseCommand implements LoggableOutputInterface
{
    use LoggableOutputTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
    }
}
```

### Message handler

Output logging can also be used with Symfony Messenger's message handlers by implementing the `LoggableOutputInterface`.
The name of the log file will be the snake cased version of the classname, e.g. `my_message_handler`. A custom name can
be provided by implementing the `NamedLoggableOutputInterface` instead.

```php
namespace App;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Bizkit\LoggableCommandBundle\LoggableOutput\NamedLoggableOutputInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MyMessageHandler implements MessageHandlerInterface, NamedLoggableOutputInterface
{
    use LoggableOutputTrait;

    public function __invoke(MyMessage $myMessage): void
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

The default configuration can be overridden for each individual command or message handler by using the `LoggableOutput`
[PHP attribute](https://www.php.net/manual/en/language.attributes.overview.php). Among other things, it allows you to
change which Monolog file handler is used by the output logger.

```php
namespace App;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;

#[LoggableOutput(filename: 'my_custom_name', type: 'rotating_file')]
class MyLoggableCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
    }
}
```

The PHP attribute can also be used as an alternative way to provide a custom name for the log file, in which case
implementing the `NamedLoggableOutputInterface` is not necessary.

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

    public function __invoke(MyMessage $myMessage): void
    {
        // ...
    }
}
```

Attribute options are inherited from all parent classes that have the PHP attribute declared. In case both a parent & a
child class have the same option defined, the one from the child class has precedence.

```php
namespace App;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

#[LoggableOutput(path: '%kernel.logs_dir%/messenger/{filename}.log')]
abstract class MyBaseMessageHandler implements MessageHandlerInterface, LoggableOutputInterface
{
    use LoggableOutputTrait;
}

#[LoggableOutput(filename: 'my_log_name')]
class MyMessageHandler extends MyBaseMessageHandler
{
    public function __invoke(MyMessage $myMessage): void
    {
        // ...
    }
}
```

### Doctrine annotations

If you're using a version of PHP prior to 8,
[Doctrine annotations](https://www.doctrine-project.org/projects/annotations.html) can be used instead of PHP attributes
as a way to override the default configuration.

1. Require the Doctrine annotations library with Composer:

    ```sh
    composer require doctrine/annotations
    ```

1. Enable annotations support in the configuration:

    ```yaml
    bizkit_loggable_command:
        file_handler_options:
            enable_annotations: true
    ```

The `LoggableOutput` PHP attribute also serves as the Doctrine annotation class.

```php
namespace App;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;

/**
 * @LoggableOutput(filename="my_custom_name", type="rotating_file")
 */
class MyLoggableCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
    }
}
```

Annotation options are also inherited from all parent classes.

### Adding other Monolog handlers to the output logger

To add other Monolog handlers to the output logger, in case of an inclusive channel list, add the Monolog channel
defined with the `channel_name` option to their `channels` list.

```yaml
monolog:
    handlers:
        sentry:
            type: sentry
            dsn: '%sentry_dsn%'
            channels: [ "loggable_output" ]
```

In case of an exclusive channel list, [disable the auto-exclusion](#disabling-auto-exclusion) feature for that handler.

> **NOTE:** In case of multiple output loggers, each output logger will use the same handler instance.

### Monolog channel auto-exclusion

The Monolog channel used by the bundle is automatically excluded from all other Monolog handlers with an exclusive
channel list. There's no need to manually add the channel to the list.

```yaml
monolog:
    handlers:
        main:
            # ...
            channels: [ "!event" ] # the bundle's channel is excluded automatically, no need to add it manually
```

#### Disabling auto-exclusion

If you don't want the channel to be automatically excluded from a certain handler, add it to the `channels` list
prefixed with `!!`.

```yaml
monolog:
    handlers:
        main:
            # ...
            channels: [ "!event", "!!loggable_output" ] # this will prevent the channel from being auto-excluded
```

## Handler factories

Handler factories are used to instantiate & configure the file handler used by the output logger.

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
Symfony's [autoconfigure](https://symfony.com/doc/4.4/service_container.html#the-autoconfigure-option) feature or wish
to use an alias in the configuration, tag the service with the `bizkit_loggable_command.handler_factory` tag.

```yaml
App\CustomHandlerFactory:
    # Prevents the handler factory from being tagged twice,
    # once by the autoconfigure feature & once manually
    autoconfigure: false
    tags:
        - { name: bizkit_loggable_command.handler_factory, type: custom }

bizkit_loggable_command:
    file_handler_options:
        type: custom
```

To simplify the configuring of a handler factory the bundle comes with an `AbstractHandlerFactory` class which can be
used to configure some common handler features such as the [PSR 3](https://www.php-fig.org/psr/psr-3/) log message
processor or a log formatter.

```php
namespace App;

use Bizkit\LoggableCommandBundle\HandlerFactory\AbstractHandlerFactory;
use Monolog\Handler\HandlerInterface;

class CustomHandlerFactory extends AbstractHandlerFactory
{
    protected function getHandler(array $handlerOptions): HandlerInterface
    {
        // return a monolog handler
    }
}
```

## Versioning

This project adheres to [Semantic Versioning 2.0.0](http://semver.org/).

## Reporting issues

Use the [issue tracker](https://github.com/HypeMC/loggable-command-bundle/issues) to report any issues you might have.

## License

See the [LICENSE](LICENSE) file for license rights and limitations (MIT).
