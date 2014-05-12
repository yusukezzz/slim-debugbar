<?php namespace Slim\Views;

use DebugBar\Bridge\Twig\TraceableTwigEnvironment;

class TraceableTwig extends Twig
{
    /**
     * @var \DebugBar\Bridge\Twig\TraceableTwigEnvironment
     */
    protected $environment;

    public function __construct(TraceableTwigEnvironment $env)
    {
        parent::__construct();
        $this->environment = $env;
    }

    /**
     * @return TraceableTwigEnvironment
     */
    public function getInstance()
    {
        return $this->environment;
    }
}
