<?php

namespace NinjaTables\Framework\Foundation;

use NinjaTables\Framework\Support\Arr;

class Config
{
    /**
     * The config data
     * @var array
     */
    protected $data = [];

    /**
     * Construct the Config instance
     * @param null
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Retrieve all config data
     * @return array
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Retrieve specific item from config array
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $key ? Arr::get($this->data, $key, $default) : $this->data;
    }

    /**
     * Set an item into the config array on the fly.
     * @param string $key
     * @param mkixed $value
     */
    public function set($key, $value)
    {
        Arr::set($this->data, $key, $value);
    }
}
