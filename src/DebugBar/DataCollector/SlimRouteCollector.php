<?php namespace DebugBar\DataCollector;

use Slim\Slim;

class SlimRouteCollector extends ConfigCollector
{
    /**
     * @var \Slim\Slim
     */
    protected $slim;

    /**
     * @param Slim $slim
     */
    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
        $this->setData($this->getRouteInfo());
    }

    public function getName()
    {
        return 'route';
    }

    public function getRouteInfo()
    {
        $route = $this->slim->router->getCurrentRoute();
        if (is_null($route)) {
            return [
                'name' => 'no matched route'
            ];
        }
        $method = $this->slim->request->getMethod();
        $path = $this->slim->request->getPathInfo();
        $uri = $method . ' ' . $path;
        return [
            'uri' => $uri,
            'pattern' => $route->getPattern(),
            'params' => $route->getParams() ?: '-',
            'name' => $route->getName() ?: '-',
            'conditions' => $route->getConditions() ?: '-',
        ];
    }

    public function getWidgets()
    {
        $name = $this->getName();
        $data = parent::getWidgets();
        $data['currentroute'] = [
            'icon' => 'share',
            'tooltip' => 'Route',
            'map' => "$name.uri",
            'default' => '{}',
        ];
        return $data;
    }
}
