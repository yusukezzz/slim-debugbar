<?php namespace Slim\Middleware;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\DataCollectorInterface;
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
        // add debugbar to Slim IoC container
        $this->app->container->singleton('debugbar', function()
        {
            return new SlimDebugBar($this->app);
        });
        $this->debugbar = $this->app->debugbar;
        $this->setAssetsRoute();

        $this->next->call();

        if ( ! $this->isModifiable()) {
            return;
        }

        // collect latest settings
        $setting = $this->app->container['settings'];
        $this->debugbar->addCollector(new ConfigCollector($setting));

        $html = $this->app->response->body();
        $this->app->response->body($this->modifyResponse($html));
    }

    public function isModifiable()
    {
        $content_type = $this->app->response->header('Content-Type');
        if ($content_type !== 'text/html') {
            return false;
        }

        if ($this->app->response->isRedirect()) {
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
