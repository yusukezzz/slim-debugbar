<?php namespace DebugBar\DataCollector;

class PhpVersionCollector extends PhpInfoCollector implements Renderable
{
    public function getWidgets()
    {
        return [
            "php" => [
                'icon' => 'magic',
                'tooltip' => 'PHP version',
                'map' => 'php.version',
                'default' => '',
            ]
        ];
    }
}
