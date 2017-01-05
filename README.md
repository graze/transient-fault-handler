# Transient Fault Handler

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/transient-fault-handler.svg?style=flat-square)](https://packagist.org/packages/graze/transient-fault-handler)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/transient-fault-handler/master.svg?style=flat-square)](https://travis-ci.org/graze/transient-fault-handler)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/transient-fault-handler.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/transient-fault-handler/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/transient-fault-handler.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/transient-fault-handler)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/transient-fault-handler.svg?style=flat-square)](https://packagist.org/packages/graze/transient-fault-handler)


Retries tasks that fail due to transient errors. Well suited to network requests but can retry any callable.

## Install

Via Composer

``` bash
$ composer require graze/transient-fault-handler
```

## Usage

The transient fault handler takes a detection strategy and a retry strategy.

``` php
$task = function () {
    // Task that is prone to transient errors
};

$builder = new TransientFaultHandlerBuilder();
$transientFaultHandler = $builder
    ->setDetectionStrategy(new DefaultDetectionStrategy())
    ->setRetryStrategy(new ExponentialBackoffStrategy())
    ->build();

$result = $transientFaultHandler->execute($task);
```

### Detection Strategy

When a task is tried, it will either return some value or throw an exception. The detection strategy will decide if that value/exception indicates a transient error or not. If it does, then the fault handler will be told to retry the task. if it does not, then the value/exception either indicates a success or a non-transient error that retrying wouldn't solve. In these cases, the value is returned to the caller or the exception is rethrown.

- `DefaultDetectionStrategy`: treats return values that [evaluate to false](http://php.net/manual/en/types.comparisons.php) and all exceptions as transient.
- `ExceptionDetectionStrategy`: treats all exceptions as transient and any return value as non-transient.


### Retry Strategy

If the detection strategy decides that the task should be retried, the retry strategy will decide how long to wait before doing so (the backoff period), and optionally impose a maximum number of retries on the task.

- `ExponentialBackoffStrategy`: the backoff period is chosen randomly between zero and an exponentially increasing maximum.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ make test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@graze.com instead of using the issue tracker.

## Credits

- [Jake Wright](https://github.com/jakewright)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
