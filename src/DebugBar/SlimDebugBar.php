<?php namespace DebugBar;

use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
use Slim\Slim;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\SlimInfoCollector;
use DebugBar\DataCollector\SlimLogCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Slim\Views\TraceableTwig;

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
        if ($slim->view instanceof \Slim\Views\Twig) {
            $env = new TraceableTwigEnvironment($slim->view->getInstance());
            $this->addCollector(new TwigCollector($env));
            $twig = new TraceableTwig($env);
            $slim->view($twig);
        }

        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new MemoryCollector());
    }
}