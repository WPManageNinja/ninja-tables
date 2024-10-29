<?php

namespace NinjaTables\Framework\Foundation;

use NinjaTables\Framework\Container\Contracts\BindingResolutionException;

class App
{
    /**
     * Application instance
     * 
     * @var NinjaTables\Framework\Foundation\Application
     */
    protected static $instance = null;

    /**
     * Resolve dynamically accessed class.
     * 
     * @param  string $module
     * @return string
     */
    protected static function resolve($module)
    {
        $pieces = explode('\\', __NAMESPACE__);
        array_pop($pieces) && $prefix = implode('\\', $pieces);
        return $prefix . '\\' . str_replace('.', '\\', $module);
    }

    /**
     * Set the application instance
     * 
     * @param NinjaTables\Framework\Foundation\Application $app
     */
    public static function setInstance($app)
    {
        static::$instance = $app;
    }

    /**
     * Get the application instance
     * 
     * @param  string $module The binding/key name for a component.
     * @param  array $parameters constructor dependencies if any.
     * @return NinjaTables\Framework\Foundation\Application|mixed
     */
    public static function getInstance($module = null, $parameters = [])
    {
        try {
            if ($module) {
                return static::$instance->make($module, $parameters);
            }

            return static::$instance;

        } catch (BindingResolutionException $e) {
            if (class_exists($class = static::resolve($module))) {
                return static::$instance->make($class, $parameters);
            }

            throw $e;
        }
    }

    /**
     * Retrive a component from the container
     * 
     * @param  string $module The binding/key name for a component.
     * @param  array $parameters constructor dependencies if any.
     * @return NinjaTables\Framework\Foundation\Application|mixed
     */
    public static function make($module = null, $parameters = [])
    {
        return static::getInstance($module, $parameters);
    }

    /**
     * Handle dynamic method calls
     * 
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        if ($method === 'support') {
            $param = array_splice($params, 0, 1);
            return static::getInstance(
                'Support.' . implode('.', $param), ...$params
            );
        }

        if (method_exists(static::$instance, $method)) {
            return static::$instance->{$method}(...$params);
        }

        return static::getInstance($method, ...$params);
    }
}
