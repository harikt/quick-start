<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        $di->set('aura/project-kernel:logger', $di->lazyNew('Monolog\Logger'));
        $di->set('view', $di->lazyNew('Aura\View\View'));
    }

    public function modify(Container $di)
    {
        $this->modifyLogger($di);
        $this->modifyWebRouter($di);
        $this->modifyWebDispatcher($di);
    }

    public function modifyLogger(Container $di)
    {
        $project = $di->get('project');
        $mode = $project->getMode();
        $file = $project->getPath("tmp/log/{$mode}.log");

        $logger = $di->get('aura/project-kernel:logger');
        $logger->pushHandler($di->newInstance(
            'Monolog\Handler\StreamHandler',
            array(
                'stream' => $file,
           )
        ));
    }

    public function modifyWebRouter(Container $di)
    {
        $router = $di->get('aura/web-kernel:router');

        $router->add('hello', '/')
               ->setValues(array('action' => 'hello'));
    }

    public function modifyWebDispatcher($di)
    {
        $dispatcher = $di->get('aura/web-kernel:dispatcher');

        $view = $di->get('view');
        $response = $di->get('aura/web-kernel:response');
        $request = $di->get('aura/web-kernel:request');
        $dispatcher->setObject('hello', function () use ($view, $response, $request) {
            $name = $request->query->get('name', 'Aura');

            // set the path of the templates to registry
            $view_registry = $view->getViewRegistry();
            $view_registry->set('hello', dirname(__DIR__) . '/templates/views/hello.php');
            $layout_registry = $view->getLayoutRegistry();
            $layout_registry->set('default', dirname(__DIR__) . '/templates/layouts/default.php');

            // set the view and layout to be rendered
            $view->setView('hello');
            $view->setLayout('default');

            // set data
            $view->setData(array('name' => $name));
            // render the data and set back to response
            $response->content->set($view->__invoke());
        });
    }
}
