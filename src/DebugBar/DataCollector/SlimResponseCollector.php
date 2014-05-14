<?php namespace DebugBar\DataCollector;

use Slim\Http\Response;

class SlimResponseCollector extends DataCollector implements Renderable
{
    /**
     * @var \Slim\Http\Response
     */
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        return [
            'content-type' => $this->response->header('Content-Type'),
            'status_code' => $this->response->getStatus(),
            'headers' => $this->getDataFormatter()->formatVar($this->response->headers->all()),
            'cookies' => $this->getDataFormatter()->formatVar($this->response->cookies->all()),
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        return 'response';
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    function getWidgets()
    {
        return [
            'response' => [
                'icon' => 'tags',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'response',
                'default' => '{}',
            ]
        ];
    }
}
