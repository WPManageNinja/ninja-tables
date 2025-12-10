<?php

namespace NinjaTables\Framework\Database\Orm\Casts;

use InvalidArgumentException;
use NinjaTables\Framework\Foundation\App;
use NinjaTables\Framework\Support\Collection;
use NinjaTables\Framework\Database\Orm\Castable;
use NinjaTables\Framework\Database\Orm\CastsAttributes;

class AsEncryptedCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class($arguments) implements CastsAttributes
        {
            public function __construct(array $arguments){}

            public function get($model, $key, $value, $attributes)
            {
                $collectionClass = $this->arguments[0] ?? Collection::class;

                if (! is_a($collectionClass, Collection::class, true)) {
                    throw new InvalidArgumentException(
                        'The provided class must extend ['.Collection::class.'].'
                    );
                }

                if (isset($attributes[$key])) {
                    return new $collectionClass(
                        Json::decode(
                            App::make('encryptr')->decryptString($attributes[$key])
                        )
                    );
                }

                return null;
            }

            public function set($model, $key, $value, $attributes)
            {
                if (! is_null($value)) {
                    return [
                        $key => App::make('encryptr')->encryptString(
                            Json::encode($value)
                        )
                    ];
                }

                return null;
            }
        };
    }

    /**
     * Specify the collection for the cast.
     *
     * @param  class-string  $class
     * @return string
     */
    public static function using($class)
    {
        return static::class.':'.$class;
    }
}
