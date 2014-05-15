<?php namespace DebugBar\DataCollector;

use Slim\Slim;

class SlimEnvCollector extends DataCollector implements Renderable
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
        return $this->slim->getMode();
    }

    public function getName()
    {
        return 'slim';
    }

    public function getWidgets()
    {
        $slim_version = Slim::VERSION;
        $php_version = PHP_VERSION;
        return [
            'mode' => [
                'icon' => 'info',
                'tooltip' => "Slim {$slim_version} | PHP {$php_version}",
                'map' => 'slim',
                'default' => '',
            ]
        ];
    }
}
