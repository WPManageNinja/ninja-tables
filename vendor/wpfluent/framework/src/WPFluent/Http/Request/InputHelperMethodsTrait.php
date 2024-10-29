<?php

namespace NinjaTables\Framework\Http\Request;

use NinjaTables\Framework\Support\Arr;

trait InputHelperMethodsTrait
{
    /**
     * Get an item from the request filtering by the callback
     *
     * @param  string|array|null $key
     * @param  callable $callback
     * @param  mixed $default
     * @return mixed
     */
    public function getSafe($key, $callback = null, $default = null)
    {
        $array = $result = [];
        
        $expectsArray = true;

        if(!is_array($key)) {
            $key = [$key];
            $expectsArray = false;
        }

        // Normalize all to ['field' => ['cb1', 'cb2']] style array
        if ($callback) {
            $callback = is_array($callback) ? $callback : [$callback];
            foreach ($key as $k => $field) {
                $array[$field] = $callback;
                if (str_contains($field, '*')) {
                    $array = $this->substituteWildcardKeys($array, $field);
                }
            }
        } else {
            foreach ($key as $k => $v) {
                // Add a simple closure to normalize when
                // there's no callback given for a field
                if (is_int($k)) {
                    $k = $v;
                    $v = function($v) { return $v; };
                }

                $array[$k] = is_array($v) ? $v : [$v];

                $array = $this->substituteWildcardKeys($array, $k);
            }
        }

        // Sanitize all the fields using given callbacks
        foreach ($array as $field => $callbacks) {
            // In case someone used 'cb1|cb2|cb3' style callbacks
            $callbacks = $this->mayBeFixCallbacks($callbacks);

            if (($value = $this->get($field, $default)) !== null) {
                $callbacks = is_callable($callbacks) ? [$callbacks] : $callbacks;

                while ($callback = array_shift($callbacks)) {
                    if (is_array($value)) {
                        $value = array_map($callback, $value);
                    } else {
                        $value = $callback($value);
                    }
                }

                Arr::set($result, $field, $value);
            }
        }

        // Return the first item if only one item in the array
        // because some one asked for one field, otherwise all.
        return $expectsArray ? $result : reset($result);
    }

    /**
     * Normalize wildcard rules to dotted rule, i.e:
     * key_one.*.key_two.*.key_three becomes:
     * key_one.0.key_two.0.key_three.0
     * key_one.0.key_two.1.key_three.0
     * depending on the data array.
     * 
     * @param  array $array
     * @param  string $field
     * @return array
     */
    protected function substituteWildcardKeys($array, $field)
    {
        $callback = $array[$field];

        $keys = array_map(function($v) {
            return trim($v, '.');
        }, explode('*', $field));

        $key = array_shift($keys);
        
        if ($key && ($data = $this->get($key)) && is_array($data)) {
            
            $dotted = array_keys(Arr::dot($data, $key . '.'));
            
            foreach ($dotted as $dottedField) {
                
                $r = preg_replace('/[0-9]+/', '*', $dottedField);
                
                if (preg_match("/{$field}/", $r)) {
                    
                    $array[$dottedField] = $callback;
                    
                    if (isset($array[$field])) {
                        unset($array[$field]);
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Check and fix if callbacks are given
     * as: callback1|callback2\callback3.
     * 
     * @param  array|string $callbacks
     * @return array
     */
    protected function mayBeFixCallbacks($callbacks)
    {
        $nonFunctionCallables = $functionCallables = [];
            
        foreach ($callbacks as $cb) {
            if (is_callable($cb)) {
                $nonFunctionCallables[] = $cb;
            } elseif (is_string($cb)) {
                 $functionCallables[] = explode('|', $cb);
            } elseif (is_array($cb) && !str_contains($cb[0], '::')) {
                $functionCallables[] = $cb[0];
            } elseif (is_array($cb) && str_contains($cb[0], '::')) {
                $nonFunctionCallables[] = explode('::', $cb[0]);
            }
        }

        $callbacks = array_merge(
            Arr::flatten($functionCallables), $nonFunctionCallables
        );

        return $callbacks;
    }

	/**
	 * Returns an sanitized integer
	 * @param  string $key
	 * @param  string $dafault
	 * @return [type]  using intval function
	 */
	public function getInt($key, $dafault = null)
	{
		return intval($this->get($key, $dafault));
	}

	/**
     * Retrieve input as a float value.
     *
     * @param  string|null  $key
     * @param  float  $default
     * @return float
     */
    public function getFloat($key, $default = null)
    {
        return floatval($this->get($key, $default));
    }

	/**
	 * Returns a sanitized string
	 * @param  string $key
	 * @param  string $dafault
	 * @return string using sanitize_text_field function
	 */
	public function getText($key, $dafault = null)
	{
		return sanitize_text_field($this->get($key, $dafault));
	}

	/**
	 * Returns a string as title
	 * @param  string $key
	 * @param  string $dafault
	 * @return string using sanitize_title function
	 */
	public function getTitle($key, $dafault = null)
	{
		return sanitize_title($this->get($key, $dafault));
	}

	/**
	 * Returns sanitized email
	 * 
	 * @param  string $key
	 * @param  string $dafault
	 * @return string using sanitize_email function
	 */
	public function getEmail($key, $dafault = null)
	{
		return sanitize_email($this->get($key, $dafault));
	}

	/**
	 * Returns boolean value
	 * 
	 * @param  string $key
	 * @param  string $dafault
	 * @return bool TRUE for "1", "true", "on" and "yes"
	 * @return bool FALSE for "0", "false", "off" and "no"
	 */
	public function getBool($key, $dafault = null)
	{
		return filter_var(
			$this->get($key, $dafault),
			FILTER_VALIDATE_BOOLEAN,
			FILTER_NULL_ON_FAILURE
		);
	}
}
