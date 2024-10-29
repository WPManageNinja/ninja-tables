<?php

namespace NinjaTables\Framework\Support;

use stdClass;

class StdObject
{
	/**
	 * Creates an stdClass from an array
	 * 
	 * @param  array $array
	 * @return stdClass
	 */
	public static function create(array $array)
	{
		$object = new stdClass;
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->{$key} = call_user_func(__METHOD__, $value);
            } else {
                $object->{$key} = $value;
            }
        }

        return $object;
	}

	/**
	 * Transforms an stdClass to array
	 * 
	 * @param  stdClass $object
	 * @return array
	 */
	public static function toArray($object)
	{
		$array = [];

        foreach ($object as $key => $value) {
            if ($value instanceof stdClass) {
                $array[$key] = call_user_func(__METHOD__, $value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
	}
}
