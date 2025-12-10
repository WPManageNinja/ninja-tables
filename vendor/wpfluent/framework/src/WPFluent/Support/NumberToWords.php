<?php

namespace NinjaTables\Framework\Support;

use TypeError;
use RangeException;

/**
 * Class NumberToWords
 *
 * This class provides functionality to convert numbers
 * into their English word representation (in words).
 */
class NumberToWords
{
    /** @var array $units The words for numbers 0-19. */
    private $units = [
        '',
        'one',
        'two',
        'three',
        'four',
        'five',
        'six',
        'seven',
        'eight',
        'nine',
        'ten',
        'eleven',
        'twelve',
        'thirteen',
        'fourteen',
        'fifteen',
        'sixteen',
        'seventeen',
        'eighteen',
        'nineteen'
    ];

    /** @var array $tens The words for multiples of ten. */
    private $tens = [
        '',
        'ten',
        'twenty',
        'thirty',
        'forty',
        'fifty',
        'sixty',
        'seventy',
        'eighty',
        'ninety'
    ];

    /** @var array $largeNumbers The words for large number groups. */
    private $largeNumbers = [
        '',
        'thousand',
        'million',
        'billion',
        'trillion',
        'quadrillion',
        'quintillion',
        'sextillion',
        'septillion',
        'octillion',
        'nonillion',
        'decillion',
        'undecillion',
        'duodecillion',
        'tredecillion',
        'quattuordecillion',
        'quindecillion',
        'sexdecillion',
        'septendecillion',
        'octodecillion',
        'novemdecillion',
        'vigintillion'
    ];

    /**
     * Convert a number to its word representation.
     *
     * @param mixed $num The number to convert. Can be an integer or a float.
     * @return string|false The word representation of the number, or false if input is invalid.
     */
    public function inWords($num, $options = [])
    {
        $defaults = [
            'thousand_separator' => ',',
            'decimal_separator' => '.',
        ];

        $options = array_merge($defaults, $options);

        foreach ($options as $key => $value) {
            if (!isset($options[$key]) || !$options[$key]) {
                $options[$key] = $defaults[$key];
            }
        }

        $pos = strrpos($num, $options['decimal_separator']);

        if ($pos !== false) {
            $num = substr_replace($num, '.', $pos, 1);
            $numBeforeDec = substr($num, 0, $pos);
            $numBeforeDec = str_replace(
                $options['thousand_separator'], ',', $numBeforeDec
            );
            $num = $numBeforeDec . substr($num, $pos);
        } else {
            $num = str_replace(
                $options['thousand_separator'], ',', $num
            );
        }

        $num = $this->validateNumber($num);

        if ($num == 0) return 'zero dollar';

        // Separate the whole number and decimal part
        $parts = explode('.', (string)$num);
        $wholeNumber = (int)$parts[0];
        $cents = isset($parts[1]) ? (int)substr($parts[1], 0, 2) : 0;

        $words = [];
        $numLength = strlen((string)$wholeNumber);
        $levels = (int)(($numLength + 2) / 3);
        $maxLength = $levels * 3;
        $wholeNumber = substr('00' . $wholeNumber, -$maxLength);
        $numLevels = str_split($wholeNumber, 3);

        for ($i = 0; $i < count($numLevels); $i++) {
            $levels--;

            // Get the hundreds digit
            $hundreds = (int)($numLevels[$i] / 100);

            // Get the last two digits (tens and ones)
            $tens = (int)($numLevels[$i] % 100);

            // Create the word for hundreds
            $hundredsWord = $hundreds ? $this->units[$hundreds] . ' hundred' : '';

            // Create the word for tens and ones
            $tensWord = '';
            if ($tens < 20) {
                $tensWord = $tens ? $this->units[$tens] : '';
            } else {
                $tensWord = $this->tens[(int)($tens / 10)];
                $onesWord = $this->units[$tens % 10];
                $tensWord .= $onesWord ? ' ' . $onesWord : '';
            }

            // Combine hundreds and tens/ones
            $wordsPart = trim($hundredsWord . ' ' . $tensWord);
            // Append large number group if exists
            if ($levels && (int)($numLevels[$i])) {
                $wordsPart .= ' ' . $this->largeNumbers[$levels];
            }

            // Store the combined words for this part
            if ($wordsPart) {
                $words[] = trim($wordsPart);
            }
        }

        // Add "and" only before the last unit and make string
        $result = $this->insertAndBeforeLastUnit($words);

        // Add the cents part if it exists
        $result = $this->addCentsIfExists($result, $cents);

        // Fix spacing issues by trimming individual parts
        // and remove redundant ands and add currency name.
        return $this->fixSpacingIssuesAndAddCurrencyName($result);
    }

    /**
     * Validate the number.
     * 
     * @param  numeric $num The number to validate
     * @return mixed $num
     */
    private function validateNumber($num)
    {
        // Remove commas and spaces, then trim
        $num = str_replace([',', ' '], '', trim($num));

        // Check if the input is numeric
        if (!is_numeric($num)) {
            throw new TypeError('Input must be a numeric type.');
        }

        // Range checks
        if (is_int($num) && $num > PHP_INT_MAX) {
            throw new RangeException(
                "The Number can't be greater than " . PHP_INT_MAX
            );
        } elseif ($num > PHP_FLOAT_MAX) {
            throw new RangeException(
                "The Number can't be greater than " . PHP_FLOAT_MAX
            );
        }

        // Handle scientific notation properly
        if (stripos($num, 'E') !== false) {
            // Directly convert scientific notation to float
            $num = sprintf('%.f', floatval($num));
        } else {
            // Ensure we handle large numbers correctly
            $num = sprintf('%.f', $num);
        }

        return $num;
    }

    /**
     * Convert cents to words.
     *
     * @param int $cents The cents to convert (should be between 0 and 99).
     * @return string The word representation of the cents.
     */
    private function convertCents(int $cents): string
    {
        if ($cents < 20) {
            return $this->units[$cents] . ($cents == 1 ? ' cent' : ' cents');
        }

        $tensWord = $this->tens[(int)($cents / 10)];

        $onesWord = $this->units[$cents % 10];

        return trim($tensWord . ($onesWord ? ' ' . $onesWord : '') . ' cents');
    }

    /**
     * Add the and before the last unit.
     * @param  array $words
     * @return string
     */
    private function insertAndBeforeLastUnit($words)
    {
        if (count($words) > 1) {

            $last = array_pop($words);
            
            $pices = explode(' ', $last);
            
            if ($pices > 2) {
                $last = array_pop($pices);
                $pices = array_merge($pices, ['and', $last]);
                $words = array_merge($words, $pices);
                return trim(implode(' ', $words));
            }

            $words = array_merge($words, ['and', $last]);
        }

        return trim(implode(' ', $words));
    }

    /**
     * Add cents if exists.
     * 
     * @param string $result
     * @param int|float $cents
     * @return string
     */
    private function addCentsIfExists($result, $cents)
    {
        if ($cents > 0) {
            $centsWords = $this->convertCents($cents);
            $result = preg_replace('/( and )+/', ' ', $result);
            $result .= ' and ' . $centsWords;
        }

        return $result;
    }

    /**
     * Fix spacing issues and add currency name.
     * 
     * @param  string $result
     * @return string
     */
    private function fixSpacingIssuesAndAddCurrencyName($result)
    {
        $result = preg_replace('/\s+/', ' ', $result);
        
        if (str_starts_with($result, ' and ')) {
            $result = str_replace(' and ', '', $result);
        }

        if (str_contains($result, 'cent')) {
            if (str_contains($result, ' and ')) {
                $result = str_replace(' and ', ' dollar and ', $result);
            }
        } else {
            $result .= ' dollar';
        }

        return $result;
    }
}
