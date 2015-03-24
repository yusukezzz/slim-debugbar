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

    /**
     * @var string
     */
    protected $storage_path;

    public function setUp()
    {
        $this->storage_path = __DIR__ . '/storage';
        $this->cleanupStorage();
        $this->slim = new \Slim\Slim();
        $this->debugbar = new \Slim\Middleware\DebugBar();
        $this->debugbar->setApplication($this->slim);
    }

    public function tearDown()
    {
        $this->slim = null;
        $this->debugbar = null;
        $this->cleanupStorage();
    }

    protected function cleanupStorage()
    {
        if (is_dir($this->storage_path)) {
            $files = glob($this->storage_path . '/*', GLOB_MARK);
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->storage_path);
        }
    }

    public function test_isModifiable()
    {
        $this->assertTrue($this->debugbar->isModifiable());
    }

    public function test_isModifiable_return_false_when_not_html_response()
    {
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
        ]);
        $this->slim->response->header('Content-Type', 'image/png');
        $this->assertFalse($this->debugbar->isModifiable());
    }

    public function test_isModifiable_return_false_when_redirect()
    {
        $this->slim->response->redirect('hoge');
        $mock_debugbar = $this->getDebugBarMock($isSessionStarted = false);
        $this->debugbar->setDebugBar($mock_debugbar);
        $this->assertFalse($this->debugbar->isModifiable());
    }

    public function test_isModifiable_call_stackData_when_redirect_and_session_started()
    {
        $this->slim->response->redirect('hoge');
        $mock_debugbar = $this->getDebugBarMock($isSessionStarted = true);
        $mock_debugbar->expects($this->once())->method('stackData');
        $this->debugbar->setDebugBar($mock_debugbar);
        $this->assertFalse($this->debugbar->isModifiable());
    }

    public function test_modifyResponse_append_end_of_text_when_plain_text()
    {
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
        ]);
        $this->debugbar->setDebugBar(new \DebugBar\StandardDebugBar());
        $html = 'hoge';
        $res = $this->debugbar->modifyResponse($html);
        $pattern = '#' . $html . preg_quote($this->debugbar->getDebugHtml(), '#') . '#';
        $this->assertRegExp($pattern, $res);
    }

    public function test_modifyResponse_append_before_body_end_tag_when_html()
    {
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
        ]);
        $this->debugbar->setDebugBar(new \DebugBar\StandardDebugBar());
        $html = '</body>';
        $res = $this->debugbar->modifyResponse($html);
        $pattern = '#' . preg_quote($this->debugbar->getDebugHtml(), '#') . $html . '#';
        $this->assertRegExp($pattern, $res);
    }

    public function test_modifyResponse_sub_directory()
    {
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'SCRIPT_NAME' => '/sub',
        ]);
        $this->debugbar->setDebugBar(new \DebugBar\StandardDebugBar());
        $html = 'hoge';
        $res = $this->debugbar->modifyResponse($html);
        $pattern = '#/sub/_debugbar#';
        $this->assertRegExp($pattern, $res);
    }

    public function test_fonts_asset_route()
    {
        $slim = $this->dispatch('/_debugbar/fonts/fontawesome-webfont.woff?v=4.0.3');
        $this->assertSame('application/octet-stream; charset=binary', $slim->response->header('Content-Type'));
    }

    public function test_css_asset_route()
    {
        $slim = $this->dispatch('/_debugbar/resources/dump.css');
        $this->assertSame('text/css', $slim->response->header('Content-Type'));
    }

    public function test_js_asset_route()
    {
        $slim = $this->dispatch('/_debugbar/resources/dump.js');
        $this->assertSame('text/javascript', $slim->response->header('Content-Type'));
    }

    public function test_open_handler_route()
    {
        mkdir($this->storage_path);
        $config = ['debugbar.storage' => new \DebugBar\Storage\FileStorage($this->storage_path)];
        $slim = $this->dispatch('/_debugbar/openhandler', $config);
        $this->assertSame('application/json', $slim->response->header('Content-Type'));
    }

    public function test_open_handler_save_data_when_ajax_with_storage_config()
    {
        $this->assertFalse(is_dir($this->storage_path));
        \Slim\Environment::mock([
            'X_REQUESTED_WITH' => 'XMLHttpRequest', // ajax request
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/hoge',
        ]);
        $slim = new \Slim\Slim(['debugbar.storage' => new \DebugBar\Storage\FileStorage($this->storage_path)]);
        $slim->add($this->debugbar);
        $slim->get('/hoge', function(){ echo 'hoge'; });
        $slim->run();
        $files = glob($this->storage_path . '/*.json', GLOB_MARK);
        $this->assertNotEmpty($files[0]);
    }

    public function test_open_handler_dont_save_data_when_ajax_without_storage_config()
    {
        $this->assertFalse(is_dir($this->storage_path));
        \Slim\Environment::mock([
            'X_REQUESTED_WITH' => 'XMLHttpRequest', // ajax request
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/hoge',
        ]);
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->get('/hoge', function(){ echo 'hoge'; });
        $slim->run();
        $files = glob($this->storage_path . '/*.json', GLOB_MARK);
        $this->assertEmpty($files);
    }

    public function test_open_handler_route_dont_save_data()
    {
        mkdir($this->storage_path);
        $path = '/_debugbar/openhandler';
        $config = ['debugbar.storage' => new \DebugBar\Storage\FileStorage($this->storage_path)];
        $slim = $this->dispatch($path, $config);
        $this->assertSame(200, $slim->response->getStatus());
        $files = glob($this->storage_path . '/*.json', GLOB_MARK);
        $this->assertEmpty($files);
    }

    public function test_prepareDebugBar_initialize_when_set_SlimDebugBar_instance()
    {
        $debugbar = $this->getMockBuilder('\\DebugBar\\SlimDebugBar')
            ->setMethods(['initCollectors'])->getMock();
        $debugbar->expects($this->once())->method('initCollectors');
        $this->debugbar->setDebugBar($debugbar);
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/_debugbar/resources/icons.png',
        ]);
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->run();
        $this->assertSame(200, $this->slim->response->getStatus());
    }

    public function test_prepareDebugBar_not_initialize_when_not_set_SlimDebugBar_instance()
    {
        $debugbar = $this->getMockBuilder('\\DebugBar\\DebugBar')
            ->setMethods(['initCollectors'])->getMock();
        $debugbar->expects($this->never())->method('initCollectors');
        $this->debugbar->setDebugBar($debugbar);
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => '/_debugbar/resources/icons.png',
        ]);
        $slim = new \Slim\Slim();
        $slim->add($this->debugbar);
        $slim->run();
        $this->assertSame(200, $this->slim->response->getStatus());
    }

    /**
     * @param $isSessionStarted bool
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getDebugBarMock($isSessionStarted)
    {
        $httpDriver = $this->getMock('\\DebugBar\\PhpHttpDriver');
        $httpDriver->expects($this->any())->method('isSessionStarted')->willReturn($isSessionStarted);
        $debugbar = $this->getMock('\\DebugBar\\DebugBar');
        $debugbar->expects($this->any())->method('getHttpDriver')->willReturn($httpDriver);
        return $debugbar;
    }

    /**
     * @param $path string
     * @param $config array
     * @return \Slim\Slim
     */
    public function dispatch($path, $config = [])
    {
        \Slim\Environment::mock([
            'REQUEST_METHOD' => 'HEAD', // ignore console output
            'PATH_INFO' => $path,
        ]);
        $slim = new \Slim\Slim($config);
        $slim->add($this->debugbar);
        $slim->run();
        return $slim;
    }
}
