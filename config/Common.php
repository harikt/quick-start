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
        $this->modifyTemplates($di);
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
            $view->setView('hello');
            $view->setLayout('default');
            $view->setData(array('name' => $name));
            $response->content->set($view->__invoke());
        });
    }

    public function modifyTemplates($di)
    {
        $view = $di->get('view');
        $view_registry = $view->getViewRegistry();
        $view_directory = dirname(__DIR__) . '/templates/views/';
        $iterator = new \DirectoryIterator($view_directory);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $view_registry->set($fileinfo->getBasename('.php'), $fileinfo->getPathname());
            }
        }

        $layout_registry = $view->getLayoutRegistry();
        $layout_directory = dirname(__DIR__) . '/templates/layouts/';
        $iterator = new \DirectoryIterator($layout_directory);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $layout_registry->set($fileinfo->getBasename('.php'), $fileinfo->getPathname());
            }
        }
    }
}
