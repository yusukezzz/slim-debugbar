<?php namespace DebugBar;

use Slim\Slim;

class SlimHttpDriver extends PhpHttpDriver
{
    /**
     * @var Slim
     */
    protected $slim;

    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $val) {
            $this->slim->response->header($key, $val);
        }
    }
}