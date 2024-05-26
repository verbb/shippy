# Logging
Shippy includes [PSR-3](https://www.php-fig.org/psr/psr-3/) logging throughout the various requests to APIs. You can use the interface to add your own application's logging to Shippy, to have logging routed through there.

For example, we might like to use the popular [Monolog](https://github.com/Seldaek/monolog) package for logging. Maybe our application already uses it.

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use verbb\shippy\Shippy;

$logger = new Logger('Shippy');
$logger->pushHandler(new StreamHandler('shippy.log'));

Shippy::setLogger($logger);
```

With this in place, all logging that Shippy does will be routed through your Monolog logger, and into a `shippy.log` file within your application. This will include debug and error logging, which is configurable through your Monolog `Logger` instance.

