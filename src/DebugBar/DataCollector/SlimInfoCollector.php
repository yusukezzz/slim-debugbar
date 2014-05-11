<?php namespace DebugBar\DataCollector;

use Slim\Slim;

class SlimInfoCollector extends DataCollector implements Renderable
{
    /**
     * @var \Slim\Slim
     */
    protected $slim;

    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    public function collect()
    {
        return [
            'version' => Slim::VERSION,
            'mode' => $this->slim->getMode(),
        ];
    }

    public function getName()
    {
        return 'slim_info';
    }

    public function getWidgets()
    {
        return [
            'version' => [
                'icon' => 'info',
                'tooltip' => 'Slim version',
                'map' => 'slim_info.version',
                'default' => '',
            ],
            'mode' => [
                'icon' => 'desktop',
                'tooltip' => 'Slim mode',
                'map' => 'slim_info.mode',
                'default' => '',
            ]
        ];
    }
}
