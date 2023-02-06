<?php

namespace NinjaTables\Framework\Foundation;

use NinjaTables\Framework\View\View;
use NinjaTables\Framework\Http\Router;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Response\Response;
use NinjaTables\Framework\Database\Orm\Model;
use NinjaTables\Framework\Validator\Validator;
use NinjaTables\Framework\Foundation\Dispatcher;
use NinjaTables\Framework\Foundation\RequestGuard;
use NinjaTables\Framework\Pagination\AbstractPaginator;
use NinjaTables\Framework\Database\ConnectionResolver;
use NinjaTables\Framework\Database\Query\WPDBConnection;
use NinjaTables\Framework\Foundation\UnAuthorizedException;

class ComponentBinder
{
    protected $app = null;

    protected $bindables = [
        'Request',
        'Response',
        'Validator',
        'View',
        'Events',
        'DataBase',
        'Router',
        'Paginator'
    ];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function bindComponents()
    {
        foreach ($this->bindables as $value) {
            $method = "bind{$value}";
            $this->{$method}();
        }

        $this->extendBindings($this->app);

        $this->loadGlobalFunctions($this->app);

        $this->registerResolvingEvent($this->app);
    }

    protected function registerResolvingEvent($app)
    {
        $app->resolving(RequestGuard::class, function($request) use ($app) {

            $request->merge($request->beforeValidation());

            $data = $request->validate($app->make('validator'));

            $request->merge($request->afterValidation());
        });
    }

    protected function bindRequest()
    {
        $this->app->singleton(Request::class, function ($app) {
            return new Request($app, $_GET, $_POST, $_FILES);
        });

        $this->app->alias(Request::class, 'request');
    }

    protected function bindResponse()
    {
        $this->app->singleton(Response::class, function($app) {
            return new Response($app);
        });

        $this->app->alias(Response::class, 'response');
    }

    protected function bindValidator()
    {
        $this->app->bind(Validator::class, function($app) {
            return new Validator;
        });

        $this->app->alias(Validator::class, 'validator');
    }

    protected function bindView()
    {
        $this->app->bind(View::class, function($app) {
            return new View($app);
        });

         $this->app->alias(View::class, 'view');
    }

    protected function bindEvents()
    {
        $this->app->singleton(Dispatcher::class, function($app) {
            return new Dispatcher($app);
        });

        $this->app->alias(Dispatcher::class, 'events');
    }

    protected function bindDataBase()
    {
        $this->app->bindShared('db', function($app) {
            return new WPDBConnection(
                $GLOBALS['wpdb'], $app->config->get('database')
            );
        });

        Model::setEventDispatcher($this->app['events']);
        
        Model::setConnectionResolver(new ConnectionResolver);
    }

    protected function bindRouter()
    {
        $this->app->singleton('router', function($app) {
            return new Router($app);
        });
    }

    protected function bindPaginator()
    {
        AbstractPaginator::currentPathResolver(function () {
            return $this->app['request']->url();
        });
        
        AbstractPaginator::currentPageResolver(function ($pageName = 'page') {
            $page = $this->app['request']->get($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return $page;
            }

            return 1;
        });
    }

    protected function extendBindings($app)
    {
        $bindings = $app['path'] . 'boot/bindings.php';

        if (is_readable($bindings)) {
            require_once $bindings;
        }
    }

    protected function loadGlobalFunctions($app)
    {
        $globals = $app['path'] . 'boot/globals.php';
        
        if (is_readable($globals)) {
            require_once $globals;
        }
    }
}
