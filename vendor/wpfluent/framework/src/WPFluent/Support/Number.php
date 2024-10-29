<?php

namespace NinjaTables\Framework\Support;

use RangeException;
use TypeError;

class Number
{
	/**
	 * Format a number depending on the locale
	 * 
	 * @param  int|float $value
	 * @param  integer $dec
	 * @return Formatted number
	 * @see    https://developer.wordpress.org/reference/functions/number_format_i18n
	 */
	public static function format($value, $dec = 0)
	{
		return number_format_i18n($value, $dec);
	}

	/**
	 * Format a number as int
	 * 
	 * @param  int|float $value
	 * @return int
	 */
	public static function toInt($val)
	{
		return intval($val);
	}

	/**
	 * Format a number as float
	 * 
	 * @param  int|float $value
	 * @param  integer $dec
	 * @return float
	 */
	public static function toFloat($val, $dec = 2)
	{
	    return round((float) $val, $dec);
	}

	/**
	 * Convert a number to bool
	 * 
	 * @param  int $val
	 * @return bool
	 */
	public static function toBool($val)
	{
		return boolval(intval($val));
	}

	/**
	 * Format a number to currency depending on the locale
	 * 
	 * @param  int|float $value
	 * @param  array $before Options fpr formatting
	 * @return Formatted number with currency symbol
	 */
	public static function toCurrency($value, $options = [])
	{
	    $defaults = [
	        'locale' => get_locale(),
	        'currency_symbol' => '$',
	        'number_of_decimals' => 2,
	        'space_with_currency' => 0,
	        'currency_position' => 'left',
	    ];

	    $args = wp_parse_args($options, $defaults);

	    // Switch to the specified locale
	    $originalLocale = get_locale();
	    switch_to_locale($args['locale']);

	    // Format the number with the specified number of decimals
	    $formattedNumber = static::format($value, $args['number_of_decimals']);
	    
	    // Switch back to the original locale
	    switch_to_locale($originalLocale);

	    // Prepare the currency symbol and spacing
	    $symbol = $args['currency_symbol'];
	    $space = $args['space_with_currency'] ? ' ' : '';

	    // Return the formatted currency string based
	    // on the position of the currency symbol
	    if ($args['currency_position'] === 'left') {
	        return $symbol . $space . $formattedNumber;
	    } else {
	        return $formattedNumber . $space . $symbol;
	    }
	}

	/**
	 * Notation to numbers.
	 *
	 * This function transforms the php.ini notation
	 * for numbers (like '2M') to an integer.
	 *
	 * @param  string $size Size value.
	 * @return int
	 */
	public static function notationToNum($num)
	{
		$l = substr($num, -1);
	    $ret = (int) substr($num, 0, -1);

	    switch (strtoupper($l)) {
	        case 'P':
	            $ret *= 1024;
	            // No break.
	        case 'T':
	            $ret *= 1024;
	            // No break.
	        case 'G':
	            $ret *= 1024;
	            // No break.
	        case 'M':
	            $ret *= 1024;
	            // No break.
	        case 'K':
	            $ret *= 1024;
	            break; // Added for clarity
	    }

	    return $ret;
	}

	/**
	 * Calculates the percentage/$percent from the $value/$total
	 * 
	 * @param  int|float $percent
	 * @param  int|float $total
	 * @return int|float
	 */
	public static function getPercentage($percent, $total)
	{
		return ($percent / 100) * $total;
	}

	/**
	 * Converts a number of bytes to human readable format
	 * using the maximum unit available to convert the bytes.
	 * 
	 * @param  int|float $bytes
	 * @param  integer $decimals
	 * @return Formatted size units of bytes, i.e: 1mb/1gb e.t.c.
	 */
	public static function formatBytes($bytes, $decimals = 0)
	{
		return size_format($bytes, $decimals);
	}

	/**
	 * Makes an ordinal number from the integer
	 * 
	 * @param  int $number
	 * @return The ordinal number, i.e: 1st, 5th e.t.c.
	 */
	public static function toOrdinal($number)
	{
	    if (!is_numeric($number)) {
	        return $number;
	    }

	    if (($number % 100) >= 11 && ($number % 100) <= 13) {
	        $suffix = 'th';
	    } else {
	        switch ($number % 10) {
	            case 1:
	                $suffix = 'st';
	                break;
	            case 2:
	                $suffix = 'nd';
	                break;
	            case 3:
	                $suffix = 'rd';
	                break;
	            default:
	                $suffix = 'th';
	                break;
	        }
	    }

	    return $number . $suffix;
	}

	/**
     * Convert the number to its human readable equivalent.
     *
     * @param  int  $number
     * @param  int  $precision
     * @param  int|null  $maxPrecision
     * @return string
     */
    public static function forHumans(
    	$number, $precision = 0, $maxPrecision = null, $abbr = false
    )
    {
        return static::summarize($number, $precision, $maxPrecision, $abbr ? [
            3 => 'K',
            6 => 'M',
            9 => 'B',
            12 => 'T',
            15 => 'Q',
        ] : [
            3 => ' thousand',
            6 => ' million',
            9 => ' billion',
            12 => ' trillion',
            15 => ' quadrillion',
        ]);
    }

    /**
     * Convert the number to its human readable equivalent.
     *
     * @param  int  $number
     * @param  int  $precision
     * @param  int|null  $maxPrecision
     * @param  array  $units
     * @return string
     */
    protected static function summarize(
    	$number, $precision = 0, $maxPrecision = null, $units = []
    )
    {
        if (empty($units)) {
            $units = [
                3 => 'K',
                6 => 'M',
                9 => 'B',
                12 => 'T',
                15 => 'Q',
            ];
        }

        switch (true) {
            case floatval($number) === 0.0:
                return $precision > 0 ? static::format(0, $precision, $maxPrecision) : '0';
            case $number < 0:
                return sprintf('-%s', static::summarize(abs($number), $precision, $maxPrecision, $units));
            case $number >= 1e15:
                return sprintf('%s'.end($units), static::summarize($number / 1e15, $precision, $maxPrecision, $units));
        }

        $numberExponent = floor(log10($number));
        $displayExponent = $numberExponent - ($numberExponent % 3);
        $number /= pow(10, $displayExponent);

        return trim(
        	sprintf('%s%s', static::format(
        		$number, $precision, $maxPrecision
        	), $units[$displayExponent] ?? '')
        );
    }

	/**
	 * Converts a numeric number in words
	 * 
	 * @param  int|float $number
	 * @param  boolean $round whether to round (float to int)
	 * @param  boolean $inCents whether the result should say
	 * "and n cents" for the fraction.
	 * 
	 * @return string number in words (in human readable words),
	 * i.e: from 199900010500.91 to:
	 * one hundred and ninety nine billion nine hundred million
	 * ten thousand five hundred and ninety one cents.
	 * 
	 * @throws \RangeException
	 */
	public static function inWords($number, $options = [])
	{
		return (new NumberToWords)->inWords($number, $options);
	}

	public static function toWord($number)
	{
		$numberToWords = new NumberToWords();

		return $numberToWords->toWord($number);
	}

	/**
	 * Make words from integers
	 * 
	 * @param  int $number
	 * @param  array $numberWords
	 * @param  array $tensWords
	 * @return string
	 */
	protected static function spellInteger($number, $numberWords, $tensWords)
	{
	    $result = '';

	    $result .= static::convertToWords($number, $numberWords, $tensWords);

	    return trim($result);
	}

	/**
	 * Make words from fraction part
	 * 
	 * @param  int $number
	 * @param  array $numberWords
	 * @return string
	 */
	protected static function spellDecimal($number, $numberWords, $decimalSeparator)
	{
	    $result = '';

	    $decimalPosition = strpos($number, $decimalSeparator);
	    
	    if ($decimalPosition !== false) {
	        
	        $decimalAsString = substr($number, $decimalPosition + 1);
	        
	        for ($i = 0; $i < strlen($decimalAsString); $i++) {

	            $digit = intval($decimalAsString[$i]);

	            $result .= $numberWords[$digit] . ' ';
	        }
	    } else {
	        $result .= 'zero';
	    }

	    return trim($result);
	}

	/**
	 * Main function to make the unit words
	 * 
	 * @param  int|float $number
	 * @param  array $numberWords
	 * @param  array $tensWords
	 * @return string
	 */
	protected static function convertToWords($number, $numberWords, $tensWords)
	{
	    if ($number < 20) {
	        return $numberWords[$number];

	    } elseif ($number < 100) {

	        $result = $tensWords[intval($number / 10)];

			if (intval($number) % 10 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 10, $numberWords, $tensWords
			    );
			}

			return $result;

	    } elseif ($number < 1000) {

	        $result = $numberWords[intval($number / 100)] . ' hundred';

			if (intval($number) % 100 !== 0) {
			    $result .= ' and ' . static::convertToWords(
			    	intval($number) % 100, $numberWords, $tensWords
			    );
			}

			return $result;

	    } elseif ($number < 1000000) {
	        
	        $result = static::convertToWords(
	        	intval($number / 1000), $numberWords, $tensWords
	        );

	        $result .= ' thousand';

			if (intval($number) % 1000 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 1000, $numberWords, $tensWords
			    );
			}

			return $result;

	    } elseif ($number < 1000000000) {
	        
	        $result = static::convertToWords(
	        	intval($number / 1000000), $numberWords, $tensWords
	        );

	        $result .= ' million';

			if (intval($number) % 1000000 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 1000000, $numberWords, $tensWords
			    );
			}

			return $result;

	    } elseif ($number < 1000000000000) {
	        
	        $result = static::convertToWords(
	        	intval($number / 1000000000), $numberWords, $tensWords
	        );

	        $result .= ' billion';

			if (intval($number) % 1000000000 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 1000000000, $numberWords, $tensWords
			    );
			}

			return $result;

	    } elseif ($number < 1000000000000000) {

	        $result = static::convertToWords(
	        	intval($number / 1000000000000), $numberWords, $tensWords
	        );

	        $result .= ' trillion';

			if (intval($number) % 1000000000000 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 1000000000000, $numberWords, $tensWords
			    );
			}

			return $result;

	    } elseif (intval($number) < 1000000000000000000) {

		    $result = static::convertToWords(
		    	intval($number / 1000000000000000), $numberWords, $tensWords
		    );

		    $result .= ' quadrillion';

			if (intval($number) % 1000000000000000 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 1000000000000000, $numberWords, $tensWords
			    );
			}

			return $result;

		} elseif (intval($number) < 1000000000000000000000) {

		    $result = static::convertToWords(
		    	intval($number / 1000000000000000000), $numberWords, $tensWords
		    );

		    $result .= ' quintillion';

			if (intval($number) % 1000000000000000000 !== 0) {
			    $result .= ' ' . static::convertToWords(
			    	intval($number) % 1000000000000000000, $numberWords, $tensWords
			    );
			}

			return $result;
		} else {
			throw new RangeException('Out of range', 500);
		}
	}

	/**
	 * Make words for cents
	 * 
	 * @param  int|float $cents
	 * @param  array $numberWords
	 * @param  array $tensWords
	 * @return string
	 */
    protected static function toCents($cents, $numberWords, $tensWords)
    {
	    if (array_key_exists($cents, $numberWords)) {
	    	return $numberWords[$cents];
	    } elseif (array_key_exists($cents, $tensWords)) {
	    	return $tensWords[$cents];
	    }

	    $result = '';
	    $tens = substr(floor($cents / 10) * 10, 0, 1);
	    $unitsPart = $cents % 10;

	    if (array_key_exists($tens, $tensWords)) {
	        $result .= $tensWords[$tens];
	        if ($unitsPart > 0) {
	            $result .= ' ' . $numberWords[$unitsPart];
	        }
	    } elseif (array_key_exists($unitsPart, $numberWords)) {
	        $result .= $numberWords[$unitsPart];
	    }

	    return $result;
	}
}
