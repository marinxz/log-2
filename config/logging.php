<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

use Itspire\MonologLoki\Handler\LokiHandler;
use Itspire\MonologLoki\Handler\LokiFormater;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    docker ps -qf "name=loki" | xargs -n 1 docker inspect | grep IPAddress
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'stdout', 'loki'],
            'ignore_exceptions' => false,
        ],

        'loki' => [
            'driver'         => 'monolog',
            'level'          => env('LOG_LEVEL', 'debug'),
            'handler'        => \Itspire\MonologLoki\Handler\LokiHandler::class,
            'formatter'      => \Itspire\MonologLoki\Formatter\LokiFormatter::class,
            'formatter_with' => [
                'labels' => ['app' => 'test-app-laravel', 'severity' => 'none'],
                'context' => [],
                'systemName' => env('LOKI_SYSTEM_NAME', null ),
                'extraPrefix' => env('LOKI_EXTRA_PREFIX', ''),
                'contextPrefix' => env('LOKI_CONTEXT_PREFIX', '')
            ],
            'handler_with'   => [
                'apiConfig'  => [
                    'entrypoint'  => env('LOKI_ENTRYPOINT', "http://172.21.0.3:3100"),
                    'context'     => [],
                    'labels'      => [],
                    'client_name' => 'mm-test-app',
                    'auth' => [
                        'basic' => [
                            env('LOKI_AUTH_BASIC_USER', ''), 
                            env('LOKI_AUTH_BASIC_PASSWORD', '')
                        ],
                    ],
                ],
            ],
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'stdout' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'info'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stdout',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
