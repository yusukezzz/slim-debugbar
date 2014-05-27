<?php namespace Slim\Middleware;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\HttpDriverInterface;
use DebugBar\SlimDebugBar;
use Slim\Middleware;

class DebugBar extends Middleware
{
    /**
     * Slim Application instance
     *
     * @var \Slim\Slim
     */
    protected $app;

    /**
     * Debugbar instance
     *
     * @var \DebugBar\SlimDebugBar
     */
    protected $debugbar;

    /**
     * @var HttpDriverInterface
     */
    protected $httpDriver;

    public function __construct(HttpDriverInterface $HttpDriver = null)
    {
        $this->httpDriver = $HttpDriver;
        $this->debugbar = new SlimDebugBar();
    }

    /**
     * @param DataCollectorInterface $collector
     * @throws \DebugBar\DebugBarException
     */
    public function addCollector(DataCollectorInterface $collector)
    {
        $this->debugbar->addCollector($collector);
    }

    /**
     * @param \DebugBar\DebugBar $debugbar
     */
    public function setDebugBar(\DebugBar\DebugBar $debugbar)
    {
        $this->debugbar = $debugbar;
    }

    public function call()
    {
        $this->prepareDebugBar();
        if ( ! is_null($this->httpDriver)) $this->debugbar->setHttpDriver($this->httpDriver);
        $this->setAssetsRoute();

        $this->next->call();

        if ( ! $this->isModifiable()) {
            return;
        }

        $html = $this->app->response->body();
        $this->app->response->body($this->modifyResponse($html));
    }

    public function isModifiable()
    {
        if ($this->app->response->isRedirect()) {
            if ($this->debugbar->getHttpDriver()->isSessionStarted()) {
                $this->debugbar->stackData();
            }
            return false;
        }

        $content_type = $this->app->response->header('Content-Type');
        if (stripos($content_type, 'html') === false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $html
     * @return string
     */
    public function modifyResponse($html)
    {
        $debug_html = $this->getDebugHtml();
        $pos = mb_strripos($html, '</body>');
        if ($pos === false) {
            $html .= $debug_html;
        } else {
            $html = mb_substr($html, 0, $pos) . $debug_html . mb_substr($html, $pos);
        }
        return $html;
    }

    public function getDebugHtml()
    {
        $renderer = $this->debugbar->getJavascriptRenderer();
        return implode("\n", [$this->getCssHtml(), $this->getJsHtml(), $renderer->render()]);
    }

    public function getCssHtml()
    {
        return '<link rel="stylesheet" type="text/css" href="/_debugbar/resources/dump.css">';
    }

    public function getJsHtml()
    {
        return '<script type="text/javascript" src="/_debugbar/resources/dump.js"></script>';
    }

    protected function prepareDebugBar()
    {
        $this->debugbar->initCollectors($this->app);
        // add debugbar to Slim IoC container
        $this->app->container->set('debugbar', $this->debugbar);
    }

    protected function setAssetsRoute()
    {
        $renderer = $this->debugbar->getJavascriptRenderer();
        $this->app->get('/_debugbar/fonts/:file', function($file) use ($renderer)
        {
            // e.g. $file = fontawesome-webfont.woff?v=4.0.3
            $files = explode('?', $file);
            $file = reset($files);
            $path = $renderer->getBasePath() . '/vendor/font-awesome/fonts/' . $file;
            $this->app->response->header('Content-Type', (new \finfo(FILEINFO_MIME))->file($path));
            echo file_get_contents($path);
        });
        $this->app->get('/_debugbar/resources/:file', function($file) use ($renderer)
        {
            $files = explode('.', $file);
            $ext = end($files);
            if ($ext === 'css') {
                $this->app->response->header('Content-Type', 'text/css');
                $renderer->dumpCssAssets();
            } elseif ($ext === 'js') {
                $this->app->response->header('Content-Type', 'text/javascript');
                $renderer->dumpJsAssets();
            } else {
                $this->app->response->header('Content-Type', 'image/png');
                $path = $renderer->getBasePath() . '/' .$file;
                echo file_get_contents($path);
            }
        });
    }
}
