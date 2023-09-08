# minvws/timer

Utility package for measuring how long code takes to execute.

This package tracks execution time using PHP's native [hrtime](https://www.php.net/manual/en/function.hrtime.php) function. 

## Usage

There are two methods to use the timer:

### Method 1: Custom start / stop.

Provides full-control on when to start and stop the timer.

```php
$timer = Timer::start();            // MinVWS\Timer\Timer

/** @var Response $response */
$response = $next($request);

$duration = $timer->stop();

var_dump(get_class($duration));     // MinVWS\Timer\Duration
var_dump($duration->asSeconds());   // float(2.851062)
```

### Method 2: Wrap timer around the code to measure.

Provides a shortened syntax by calling a single method with a closure.

This can only be used, if you don't need to obtain the return value of the code being measured.

```php
$duration = Timer::wrap(
    callable: function () use ($testResultReport): void {
        $this->decorated->import($testResultReport);
    }
);

var_dump(get_class($duration));     // MinVWS\Timer\Duration
var_dump($duration->asSeconds());   // float(2.851062)
```

## Testing

This library contains test files which can be run by adding a testsuite to `phpunit.xml` like this:

```xml
<testsuite name="TimerPackage">
    <directory suffix="Test.php">../shared/packages/timer/tests</directory>
</testsuite>
```
