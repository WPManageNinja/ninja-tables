<?php

namespace NinjaTables\Framework\Request;

trait InputHelperMethodsTrait
{
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
