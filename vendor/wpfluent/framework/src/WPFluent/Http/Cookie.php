<?php

namespace NinjaTables\Framework\Http;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use NinjaTables\Framework\Foundation\App;

class Cookie
{
    /**
     * Cookie queue
     * @var array
     */
    protected static $queue = [];

    /**
     * Action registered iindicator.
     * 
     * @var boolean
     */
    protected static $actionRegistered = false;

    /**
     * Sets the cookie.
     * 
     * @param string  $name
     * @param mixed   $value
     * @param integer $minutes
     * @param string  $path
     * @param string  $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return void
     */
    public static function set(
        $name,
        $value,
        $minutes = 0,
        $path = '',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        $time = static::expiresAt($minutes);
        
        setcookie($name, $value, $time, $path, $domain, $secure, $httponly);
    }

    /**
     * Sets the cookie forever (5 years).
     * 
     * @param string  $name
     * @param mixed   $value
     * @param string  $path
     * @param string  $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return void
     */
    public static function setForever(
        $name,
        $value,
        $path = '',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        $fiveYears = time() + (5 * 365 * 24 * 60 * 60);

        setcookie($name, $value, $fiveYears, $path, $domain, $secure, $httponly);
    }

    /**
     * Add a cookie into the queue.
     * 
     * @param  string  $name
     * @param  mixed   $value
     * @param  integer $minutes
     * @param  string  $path
     * @param  string  $domain
     * @param  boolean $secure
     * @param  boolean $httponly
     * @return void
     */
    public static function queue(
        $name,
        $value,
        $minutes = 0,
        $path = '',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        if (!static::$actionRegistered) {
            static::$actionRegistered = true;
            App::addAction(
                'send_headers', [static::class, 'sendHeaders']
            );
        }

        $time = static::expiresAt($minutes);
        
        static::$queue[] = [
            'name' => $name, 
            'value' => $value,
            'expires' => $time,
            'path' => $path,
            'domain' => $domain,
            'port' => $secure,
            'host_only' => $httponly
        ];
    }

    /**
     * WordPress's send_headers action handler.
     * 
     * @param  WordPress Object $wp
     * @return void
     */
    public static function sendHeaders($wp)
    {
        foreach (static::$queue as $cookie) {
            static::set(
                $cookie['name'],
                $cookie['value'],
                $cookie['expires'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['port'],
                $cookie['host_only'],
            );
        }
    }

    /**
     * Retrieve a cookie.
     * 
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (array_key_exists($name, $_COOKIE)) {
            return $_COOKIE[$name];
        }

        return $default;
    }

    /**
     * Delete a cookie.
     * 
     * @param  string $name
     * @param  string $path
     * @param  string $domain
     * @return void
     */
    public static function delete($name, $path = '', $domain = '') {
        setcookie($name, '', time() - 3600, $path, $domain);

        if (array_key_exists($name, $_COOKIE)) {
            unset($_COOKIE[$name]);
        }
    }

    /**
     * Prepares expiration time in minutes.
     * 
     * @param  int|DateTimeInterface $minutes
     * @return int
     */
    protected static function expiresAt($minutes = 0)
    {
        if (is_int($minutes)) {
            if ($minutes === 0) {
                return $minutes;
            }

            $minutes = new DateTime('+' . $minutes . ' minutes');
        }

        if (!($minutes instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid expiration time provided.'
            );
        }

        return $minutes->getTimestamp();
    }
}
