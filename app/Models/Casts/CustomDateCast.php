<?php
namespace NinjaTables\App\Models\Casts;

use NinjaTables\Framework\Database\Orm\Castable;
use NinjaTables\Framework\Database\Orm\CastsAttributes;
use NinjaTables\Framework\Support\DateTime;

class CustomDateCast implements Castable
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
                if ($value) {
                    $date = new DateTime($value);
                    return $date->format('Y-m-d H:i:s');
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
