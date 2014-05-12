<?php namespace DebugBar;

use Slim\Slim;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\SlimInfoCollector;
use DebugBar\DataCollector\SlimLogCollector;
use DebugBar\DataCollector\TimeDataCollector;

class SlimDebugBar extends DebugBar
{
    public function __construct(Slim $slim)
    {
        $this->addCollector(new SlimLogCollector($slim));
        $this->addCollector(new SlimInfoCollector($slim));
        $slim->hook('slim.after.router', function() use ($slim)
        {
            // collect latest settings
            $setting = $slim->container['settings'];
            $this->addCollector(new ConfigCollector($setting));
        });

        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new MemoryCollector());
    }
}