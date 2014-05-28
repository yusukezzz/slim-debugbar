<?php namespace DebugBar;

use Slim\Slim;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\SlimEnvCollector;
use DebugBar\DataCollector\SlimLogCollector;
use DebugBar\DataCollector\SlimResponseCollector;
use DebugBar\DataCollector\SlimRouteCollector;
use DebugBar\DataCollector\SlimViewCollector;

class SlimDebugBar extends DebugBar
{
    public function __construct()
    {
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new MemoryCollector());
    }

    public function initCollectors(Slim $slim)
    {
        $this->addCollector(new SlimLogCollector($slim));
        $this->addCollector(new SlimEnvCollector($slim));
        $slim->hook('slim.after.router', function() use ($slim)
        {
            $setting = $this->prepareRenderData($slim->container['settings']);
            $data = $this->prepareRenderData($slim->view->all());
            $this->addCollector(new SlimResponseCollector($slim->response));
            $this->addCollector(new ConfigCollector($setting));
            $this->addCollector(new SlimViewCollector($data));
            $this->addCollector(new SlimRouteCollector($slim));
        });
    }

    protected function prepareRenderData(array $data = [])
    {
        $tmp = [];
        foreach ($data as $key => $val) {
            if (is_object($val)) {
                $val = "Object (". get_class($val) .")";
            }
            $tmp[$key] = $val;
        }
        return $tmp;
    }
}