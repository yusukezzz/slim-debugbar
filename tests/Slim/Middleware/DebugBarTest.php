<?php

class DebugBarTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Slim\Slim
     */
    protected $slim;

    /**
     * @var \Slim\Middleware\DebugBar
     */
    protected $debugbar;

    public function setUp()
    {
        $this->slim = new \Slim\Slim();
        $this->debugbar = new \Slim\Middleware\DebugBar();
        $this->debugbar->setApplication($this->slim);
    }

    public function tearDown()
    {
        $this->slim = null;
        $this->debugbar = null;
    }

    public function test_isModifiable()
    {
        $this->assertTrue($this->debugbar->isModifiable());
    }

    public function test_isModifiable_return_false_when_not_html_response()
    {
        $this->slim->response->header('Content-Type', 'image/png');
        $this->assertFalse($this->debugbar->isModifiable());
    }

    public function test_isModifiable_return_false_when_redirect()
    {
        $this->slim->response->redirect('hoge');
        $this->assertFalse($this->debugbar->isModifiable());
    }

    public function test_modifyResponse_append_end_of_text_when_plain_text()
    {
        $this->debugbar->setDebugBar(new \DebugBar\StandardDebugBar());
        $html = 'hoge';
        $res = $this->debugbar->modifyResponse($html);
        $pattern = '#' . $html . quotemeta($this->debugbar->getDebugHtml()).'#';
        $this->assertRegExp($pattern, $res);
    }

    public function test_modifyResponse_append_before_body_end_tag_when_html()
    {
        $this->debugbar->setDebugBar(new \DebugBar\StandardDebugBar());
        $html = '</body>';
        $res = $this->debugbar->modifyResponse($html);
        $pattern = '#' . quotemeta($this->debugbar->getDebugHtml()) . $html.'#';
        $this->assertRegExp($pattern, $res);
    }

    public function test_fonts_asset_route()
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/_debugbar/fonts/fontawesome-webfont.woff?v=4.0.3',
        ));
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->run();
        $this->assertSame('application/octet-stream; charset=binary', $slim->response->header('Content-Type'));
    }

    public function test_css_asset_route()
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/_debugbar/resources/dump.css',
        ));
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->run();
        $this->assertSame('text/css', $slim->response->header('Content-Type'));
    }

    public function test_js_asset_route()
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/_debugbar/resources/dump.js',
        ));
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->run();
        $this->assertSame('text/javascript', $slim->response->header('Content-Type'));
    }

    public function test_image_asset_route()
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/_debugbar/resources/icons.png',
        ));
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->run();
        $this->assertSame('image/png', $slim->response->header('Content-Type'));
    }
}
