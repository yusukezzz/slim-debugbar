## Slim DebugBar
[![Latest Stable Version](https://poser.pugx.org/yusukezzz/slim-debugbar/v/stable.png)](https://packagist.org/packages/yusukezzz/slim-debugbar)
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

    "yusukezzz/slim-debugbar": "1.*"

example

```php
<?php
require '/path/to/vendor/autoload.php';
$config = [];
// if you want to capture ajax requests, set instance of StorageInterface implemented.
// $config['debugbar.storage'] = new \DebugBar\Storage\FileStorage('/path/to/storage');
$slim = new \Slim\Slim($config);
$debugbar = new \Slim\Middleware\DebugBar();
// you can add custom collectors
//  $debugbar->addCollector(new MyCustomCollector());
// or use custom debugbar
//  $debugbar->setDebugBar(new MyCustomDebugBar());
$slim->add($debugbar);
$slim->get('/', function()
{
    echo 'Hello world!';
});
$slim->run();
```

### Notice
  * Please use real httpd (apache, nginx etc...)
      - PHP builtin server does not supported.
  * Available storage for ajax capturing
      - Filesystem, PDO and Redis
      - for more information, please refer to [the official document](http://phpdebugbar.com/docs/storage.html).
  * Redirection data stack
      - support PHP native session only (session_start() required)
      - if you want to use your own session manager, you should implement DebugBar\\HttpDriverInterface.
  * Reserved route for DebugBar
      - /_debugbar/fonts/:file
          + for fontawesome files
      - /_debugbar/resources/:file
          + for css, javascript
      - /_debugbar/openhandler
          + for previous sets of collected data

#### Custom Session Manager example

```php
<?php
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
$slim->container->singleton('session', function()
{
    return new YourSessionManager();
});
$driver = new MyHttpDriver($slim->session, $slim->response);
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

### Q&A
  * Q. Errors in the browser's console: `Resource interpreted as Font but transferred with MIME type text/html`
    - A. Please read [this solution](https://github.com/yusukezzz/slim-debugbar/issues/6#issuecomment-89362616)

## License

MIT
