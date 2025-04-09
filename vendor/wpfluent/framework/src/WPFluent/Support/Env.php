<?php

namespace NinjaTables\Framework\Support;

use RuntimeException;

class Env
{
    public static function load($filePath)
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(
                "Environment file not found at: $filePath"
            );
        }

        $lines = file(
            $filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            $parts = explode('=', $line, 2);
            
            if (count($parts) < 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1], "\"'");

            switch (strtolower(trim($value))) {
                case 'true':
                case '(true)':
                    $value = true;
                    break;
                case 'false':
                case '(false)':
                    $value = false;
                    break;
                case 'null':
                case '(null)':
                    $value = null;
                    break;
                case 'empty':
                case '(empty)':
                    $value = '';
                    break;
            }

            if (!empty($name)) {
                self::set($name, $value);
            }
        }
    }

    public static function get($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }

    public static function set($key, $value)
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }

    public static function all()
    {
        $envVars = [];

        foreach ($_ENV as $key => $value) {
            $envVars[$key] = $value;
        }

        foreach (getenv() as $key => $value) {
            if (!array_key_exists($key, $envVars)) {
                $envVars[$key] = $value;
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (
                strpos($key, 'HTTP_') === false &&
                !array_key_exists($key, $envVars)
            ) {
                $envVars[$key] = $value;
            }
        }

        return $envVars;
    }

    public static function dd()
    {
        if (function_exists('dd')) {
            dd(static::all());
        } else {
            print_r(static::all());
            die;
        }
    }
}
