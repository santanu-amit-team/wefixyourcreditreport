Monolog Parser [![Build Status](https://travis-ci.org/ddtraceweb/monolog-parser.png?branch=master)](https://travis-ci.org/ddtraceweb/monolog-parser)
==============

A simple library for parsing [monolog](https://github.com/Seldaek/monolog) logfiles.

## Installation

You can install the library using [composer]('http://getcomposer.org/) by adding  `ddtraceweb/monolog-parser` to your `composer.json`.

## Usage

* 1 days of logs

```php
    require_once 'path/to/vendor/autoload.php';

    use Dubture\Monolog\Reader\LogReader;

    $logFile = '/path/to/some/monolog.log';
    $reader = new LogReader($logFile);

    foreach ($reader as $log) {
        echo sprintf("The log entry was written at %s. \n", $log['date']->format('Y-m-d h:i:s'));
    }

    $lastLine = $reader[count($reader)-1];
    echo sprintf("The last log entry was written at %s. \n", $lastLine['date']->format('Y-m-d h:i:s'));

```

* options unlimited days logs

```php
    require_once 'path/to/vendor/autoload.php';

    use Dubture\Monolog\Reader\LogReader;

    $logFile = '/path/to/some/monolog.log';
    $reader = new LogReader($logFile, 0);

    foreach ($reader as $log) {
        echo sprintf("The log entry was written at %s. \n", $log['date']->format('Y-m-d h:i:s'));
    }

    $lastLine = $reader[count($reader)-1];
    echo sprintf("The last log entry was written at %s. \n", $lastLine['date']->format('Y-m-d h:i:s'));

```

* options 2 days logs

```php
    require_once 'path/to/vendor/autoload.php';

    use Dubture\Monolog\Reader\LogReader;

    $logFile = '/path/to/some/monolog.log';
    $reader = new LogReader($logFile, 2);

    foreach ($reader as $log) {
        echo sprintf("The log entry was written at %s. \n", $log['date']->format('Y-m-d h:i:s'));
    }

    $lastLine = $reader[count($reader)-1];
    echo sprintf("The last log entry was written at %s. \n", $lastLine['date']->format('Y-m-d h:i:s'));

```

* Add custom pattern
```php

    require_once 'path/to/vendor/autoload.php';

    use Dubture\Monolog\Reader\LogReader;

    $logFile = '/path/to/some/monolog.log';
    $reader = new LogReader($logFile);

    $pattern = '/\[(?P<date>.*)\] (?P<logger>[\w-\s]+).(?P<level>\w+): (?P<message>[^\[\{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';
    $reader->getParser()->registerPattern('newPatternName', $pattern);
    $reader->setPattern('newPatternName');

    foreach ($reader as $log) {
        echo sprintf("The log entry was written at %s. \n", $log['date']->format('Y-m-d h:i:s'));
    }

    $lastLine = $reader[count($reader)-1];
    echo sprintf("The last log entry was written at %s. \n", $lastLine['date']->format('Y-m-d h:i:s'));

```


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/ddtraceweb/monolog-parser/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

