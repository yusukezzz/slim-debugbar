<?php namespace DebugBar;

use Slim\Slim;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpVersionCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\SlimEnvCollector;
use DebugBar\DataCollector\SlimLogCollector;
use DebugBar\DataCollector\SlimRouteCollector;
use DebugBar\DataCollector\SlimViewCollector;
use DebugBar\DataCollector\TimeDataCollector;

class SlimDebugBar extends DebugBar
{
    public function __construct(Slim $slim)
    {
        $this->addCollector(new SlimLogCollector($slim));
        $this->addCollector(new SlimEnvCollector($slim));
        $slim->hook('slim.after.router', function() use ($slim)
        {
            // collect latest settings
            $setting = $this->prepareRenderData($slim->container['settings']);
            $this->addCollector(new ConfigCollector($setting));
            // collect view variables
            $data = $this->prepareRenderData($slim->view->all());
            $this->addCollector(new SlimViewCollector($data));
            // collect route infomation
            $this->addCollector(new SlimRouteCollector($slim));
        });

        $this->addCollector(new PhpVersionCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new MemoryCollector());
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