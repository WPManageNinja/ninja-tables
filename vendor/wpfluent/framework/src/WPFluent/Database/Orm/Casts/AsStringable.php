<?php

namespace NinjaTables\Framework\Database\Orm\Casts;

use NinjaTables\Framework\Support\Str;
use NinjaTables\Framework\Database\Orm\Castable;
use NinjaTables\Framework\Database\Orm\CastsAttributes;

class AsStringable implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return isset($value) ? Str::of($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                return isset($value) ? (string) $value : null;
            }
        };
    }
}
