## Slim DebugBar
[![Build Status](https://travis-ci.org/yusukezzz/slim-debugbar.svg?branch=master)](https://travis-ci.org/yusukezzz/slim-debugbar)

This middleware append [PHP Debug Bar](http://phpdebugbar.com/) to Slim response.

Inspired by [Laravel 4 DebugBar](https://github.com/barryvdh/laravel-debugbar)

![Screenshot](https://dl.dropboxusercontent.com/u/203881/2014-05-14_23.18.17.png)

### Custom Collectors

  * SlimEnvCollector (collect slim mode and version)
  * SlimViewCollector (collect view variables)
  * SlimRouteCollector (collect matched route information)
  * SlimResponseCollector (collect response headers and cookies)

### DebugBar Default Collectors

  * SlimCollector
  * PhpInfoCollector
  * ConfigCollector
  * RequestDataCollector
  * TimeDataCollector
  * MemoryCollector


### Install

Require this package in your composer.json

    "yusukezzz/slim-debugbar": "dev-master"

sample

```php
<?php
require '/path/to/vendor/autoload.php';
$slim = new \Slim\Slim();
$slim->add(new \Slim\Middleware\DebugBar());
$slim->get('/', function()
{
    echo 'Hello world!';
});
$slim->run();
```

### Notice

  * Redirection data stack
      - support PHP native session only (session_start() required)
      - if you want to use your own session manager, you should implement DebugBar\\HttpDriverInterface.
  * Reserved route for DebugBar
      - /_debugbar/fonts/:file
          + for fontawesome files
      - /_debugbar/resources/:file
          + for css, javascript, images

#### Custom Session Manager example

```php
require '/path/to/vendor/autoload.php';
class MyHttpDriver implements \DebugBar\HttpDriverInterface
{
    protected $session;
    protected $response;
    public function __construct(YourSessionManager $session, \Slim\Http\Response $response)
    {
        $this->session = $session;
        $this->response = $response;
    }
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $val) {
            $this->response->header($key, $val);
        }
    }
    public function isSessionStarted()
    {
        return $this->session->isStarted();
    }
    // You should implement other methods too
}
$slim = new \Slim\Slim();
$session = new YourSessionManager();
$driver = new MyHttpDriver($session, $slim->response);
$debugbar = new \Slim\Middleware\DebugBar($driver);
$slim->add($debugbar);
$slim->get('/', function()
{
    echo 'Hello world!';
});
$slim->get('/redirect', function() use ($slim)
{
    $slim->response->redirect('/');
});
$slim->run();
```

## License

MIT
