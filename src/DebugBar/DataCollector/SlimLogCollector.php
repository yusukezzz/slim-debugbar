<?php namespace DebugBar\DataCollector;

use DebugBar\Bridge\SlimCollector;

class SlimLogCollector extends SlimCollector
{
    public function getName()
    {
        return 'log';
    }
}
