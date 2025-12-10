<?php

namespace NinjaTables\App\Models\Casts;

use NinjaTables\Framework\Database\Orm\Castable;
use NinjaTables\Framework\Database\Orm\CastsAttributes;
use NinjaTables\Framework\Support\DateTime;

class CustomDateCast implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if ($value) {
                    $datetime = DateTime::parse($value);

                    return $datetime->format('Y-m-d H:i:s');
                }
                return $value;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [$key => $value];
            }
        };
    }
}
