<?php namespace DebugBar;

use Slim\Slim;
use DebugBar\Bridge\SlimCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\SlimInfoCollector;

class SlimDebugBar extends DebugBar
{
    public function __construct(Slim $slim)
    {
        $this->addCollector(new SlimCollector($slim));
        $this->addCollector(new SlimInfoCollector($slim));
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new MemoryCollector());
    }
}