<?php namespace DebugBar\DataCollector;

use Slim\Route;
use Slim\Router;

class SlimRouteCollector extends ConfigCollector
{
    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->setData($this->getRouteInfo($router->getCurrentRoute()));
    }

    public function getName()
    {
        return 'route';
    }

    public function getRouteInfo(Route $route)
    {
        if (is_null($route)) {
            return [
                'name' => 'no matched route'
            ];
        }
        return [
            'name' => $route->getName() ?: '-',
            'pattern' => $route->getPattern() ?: '-',
            'params' => $route->getParams() ?: '-',
            'methods' => $route->getHttpMethods() ?: '-',
            'conditions' => $route->getConditions() ?: '-',
        ];
    }
}
