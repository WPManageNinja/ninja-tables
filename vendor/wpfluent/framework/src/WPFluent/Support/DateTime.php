<?php

namespace NinjaTables\Framework\Support;

use DateTimeZone;
use DateInterval;
use DateTimeInterface;
use DateTime as PHPDateTime;
use InvalidArgumentException;

class DateTime extends PHPDateTime
{
    /**
     * $singularUnits for checking during dynamic calls
     * @var array
     */
    protected static $singularUnits = [
        'year', 'month', 'week', 'day', 'hour', 'minute', 'second',
    ];

    /**
     * $pluralUnits for checking during dynamic calls
     * @var array
     */
    protected static $pluralUnits = [
        'years','months', 'weeks', 'days', 'hours', 'minutes', 'seconds'
    ];

    /**
     * Construct the DateTime Object
     * 
     * @param string $datetime
     * @param \DateTimeZone $timezone|null
     */
    public function __construct($datetime = "now", $timezone = null)
    {
        $timezone = $timezone ?: $this->getDefaultTimezone();

        parent::__construct($datetime, $timezone);
    }

    /**
     * Create a new DateTime Object with current time
     * 
     * @return self
     */
    public static function now($tz = null)
    {
        return static::create('now', $tz);
    }

    /**
     * Create a new DateTime Object with today's time
     * 
     * @return self
     */
    public static function today($tz = null)
    {
        return static::create('today', $tz)->startOfDay();
    }

    /**
     * Create a new DateTime Object with yesterday's time
     * 
     * @return self
     */
    public static function yesterday($tz = null)
    {
        return static::create('now', $tz)->modify('-1 day')->startOfDay();
    }

    /**
     * Create a new DateTime Object with tomorrow's time
     * 
     * @return self
     */
    public static function tomorrow($tz = null)
    {
        return static::create('now', $tz)->modify('+1 day')->startOfDay();
    }

    /**
     * Get the default timezone
     *
     * @return \DateTimeZone
     */
    public function getDefaultTimezone()
    {
        return wp_timezone();
    }

    /**
     * Set the timezone
     *
     * @return self
     */
    public function timezone($tz)
    {
        if (is_string($tz)) {
            $tz = new DateTimeZone($tz);
        }

        return $this->setTimezone($tz);
    }

    /**
     * Get the default date format
     * 
     * @return string
     */
    public function getDateFormat()
    {   
        return 'Y-m-d H:i:s';
    }

    /**
     * Check if the current instance is between two dates
     *
     * @param string|DateTimeInterface $date1
     * @param string|DateTimeInterface $date2,
     * @return bool
     */
    public function between($date1, $date2)
    {
        if (!$date1 instanceof DateTimeInterface) {
            $date1 = new DateTime($date1);
        }

        if (!$date2 instanceof DateTimeInterface) {
            $date2 = new DateTime($date2);
        }

        return ($this >= $date1 && $this <= $date2);
    }

    /**
     * Create a DateTime object from a string, UNIX timestamp,
     * or other DateTimeInterface object.
     * 
     * @param  string|int|\DateTimeInterface  $time
     * @return static
     * @throws \Exception
     */
    public static function create($time = null, $tz = null)
    {
        if (func_num_args() > 2) {
            return static::createFromDate(...func_get_args());
        }

        $time = $time ?: static::now();

        if (is_null($tz)) {
            $timezone = (new static)->getDefaultTimezone();
        } else {
            $timezone = is_string($tz) ? new DateTimeZone($tz) : $tz;
        }

        if (!$timezone instanceof DateTimeZone) {
            throw new InvalidArgumentException('Invalid timezone.');
        }

        if ($time instanceof DateTimeInterface) {

            $dateTime = new static(
                $time->format((new static)->getDateFormat()), $time->getTimezone()
            );

            // Override the timezone if the timezone is explictly provided
            // otherwise don't set the default timezone from $timezone.
            !is_null($tz) && $dateTime->setTimezone($timezone);

        } elseif (is_numeric($time)) {
            if ($time <= YEAR_IN_SECONDS) {
                $time += time();
            }

            $dateTime = new static('@' . $time);

            $dateTime->setTimezone($timezone);

        } else {
            $dateTime = new static((string) $time);

            // Set the timezone if timezone is explicitly provided
            // otherwise set the default timezone if there was no
            // timezne information available with the string.
            if ($tz || !$dateTime->hasTimezone($time)) {
                $dateTime->setTimezone($timezone);
            }
        }

        return $dateTime;
    }

    /**
     * Check if the given datetime string has the timezone
     * information attached: Z or +/-00:00 or Asia\Dhaka.
     * 
     * @param  string $datetimeString
     * @return boolean
     */
    public function hasTimezone($datetimeString)
    {
        // Regular expression to match timezone
        // identifier, UTC, or timezone offset
        $pattern = '/(?:[A-Z][a-zA-Z_]+\/[a-zA-Z_]+|Z|[-+]\d{2}:\d{2})/';

        return preg_match($pattern, $datetimeString) === 1;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public static function createFromFormat($format, $datetimeString, $timezone = null)
    {
        if (is_null($timezone)) {
            $timezone = (new static)->getDefaultTimezone();
        } else {
            $timezone = is_string($timezone) ? new DateTimeZone($timezone) : $timezone;
        }

        if (!$timezone instanceof DateTimeZone) {
            throw new InvalidArgumentException('Invalid timezone.');
        }

        $dateTime = PHPDateTime::createFromFormat($format, $datetimeString);
        
        if ($dateTime !== false) {
            
            if (!$dateTime instanceof static) {
                return new static(
                    $dateTime->format(ltrim($format, '!')), $timezone
                );
            }

            $dateTime->setTimezone($timezone);

            return $dateTime;
        }

        throw new InvalidArgumentException(
            "Unable to create datetime from: {$datetimeString}."
        );
    }

    /**
     * Create DateTime object.
     * 
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createFromDate(
        $year, $month, $day, $hour = 0, $minute = 0, $second = 0.0, $tz = null
    ) {
        
        $s = sprintf(
            '%04d-%02d-%02d %02d:%02d:%02.5F', $year, $month, $day, $hour, $minute, $second
        );

        if (
            !checkdate($month, $day, $year)
            || $hour < 0
            || $hour > 23
            || $minute < 0
            || $minute > 59
            || $second < 0
            || $second >= 60
        ) {
            throw new InvalidArgumentException("Invalid date '$s'");
        }

        return new static($s, (is_string($tz) ? new DateTimeZone($tz) : $tz));
    }

    /**
     * Given a date in UTC or GMT timezone, returns
     * that date in the timezone of the site.
     *
     * Requires a date in the Y-m-d H:i:s format.
     * 
     * Default return format of 'Y-m-d H:i:s' can be
     * overridden using the `$format` parameter.
     *
     * @param string $date_string The date to be converted, in UTC or GMT timezone.
     * @param string $format      The format string for the returned date. Default 'Y-m-d H:i:s'.
     * @see https://developer.wordpress.org/reference/functions/get_date_from_gmt/
     * 
     * @return string Formatted version of the date, in the site's timezone.
     */
    public static function createFromUTC($dateString, $format = 'Y-m-d H:i:s')
    {
        $date = new static(get_date_from_gmt($dateString, $format));

        return $date->timezone($date->getDefaultTimezone())->format($format);
    }

    /**
     * Parse a datetime string
     * @param  string $datetimeString
     * @param  string $timezone
     * @return self
     * @throws InvalidArgumentException
     */
    public static function parse($datetimeString, $timezone = null)
    {
        $parsedDate = date_parse($datetimeString);
        
        $datetimeString = date('Y-m-d H:i:s', mktime(
            $parsedDate['hour'],
            $parsedDate['minute'],
            $parsedDate['second'],
            $parsedDate['month'],
            $parsedDate['day'],
            $parsedDate['year']
        ));

        if ($timezone && is_scalar($timezone)) {
            $timezone = new DateTimeZone($timezone);
        } elseif (isset($parsedDate['tz_id'])) {
            $timezone = new DateTimeZone($parsedDate['tz_id']);
        }

        $dateTime = new PHPDateTime($datetimeString, $timezone);

        if ($dateTime instanceof DateTimeInterface) {
            return new static($datetimeString, $timezone);
        }

        throw new InvalidArgumentException('Unable to handle datetime.');
    }

    /**
     * Add inetrvals, for example:
     * 
     * add(1, day)
     * add('2 day 8 hours 22 minutes')
     * 
     * @param \DateInterval|string
     * @return self
     */
    #[\ReturnTypeWillChange]
    public function add($interval)
    {
        if ($interval instanceof DateInterval) {
            return parent::add($interval);
        } elseif (func_num_args() === 1 && is_string($interval)) {
            return $this->modify('+'.$interval);
        }

        return $this->addOrSub('add', func_get_args());
    }

    /**
     * Substruct inetrvals, for example:
     * 
     * sub(1, day)
     * sub('2 day 8 hours 22 minutes')
     * 
     * @param \DateInterval $interval (optional)
     * @return self
     */
    #[\ReturnTypeWillChange]
    public function sub($interval)
    {
        if ($interval instanceof DateInterval) {
            return parent::add($interval);
        } elseif (func_num_args() === 1 && is_string($interval)) {
            return $this->modify('-'.$interval);
        }

        return $this->addOrSub('sub', func_get_args());
    }

    /**
     * Add or sub intervals
     * @param string $action add/sub
     * @param array $args
     */
    protected function addOrSub($action, $args)
    {
        $value = reset($args);
        
        $action = $action.end($args);

        return $this->{$action}($value);
    }

    /**
     * Sets start of the year in the current dateTime
     * 
     * @return self
     */
    public function startOfYear()
    {
        return $this->modify('first day of January')->startOfDay();
    }

    /**
     * Sets end of the year in the current dateTime
     * 
     * @return self
     */
    public function endOfYear()
    {
        return $this->modify('last day of December')->endOfDay();
    }

    /**
     * Sets start of the month in the current dateTime
     * 
     * @return self
     */
    public function startOfMonth()
    {
        return $this->modify('first day of this month')->startOfDay();
    }

    /**
     * Sets end of the month in the current dateTime
     * 
     * @return self
     */
    public function endOfMonth()
    {
        return $this->modify('last day of this month')->endOfDay();
    }

    /**
     * Sets start of the week in the current dateTime
     * 
     * @return self
     */
    public function startOfWeek()
    {
        $startOfWeek = intval(get_option('start_of_week'));

        $this->modify('this week');

        // If the start of the week is Sunday (0)
        if ($startOfWeek === 0) {
            return $this->modify('this Sunday')->startOfDay();
        } else {
            // If it's Monday (1), we need to subtract 1 day.
            return $this->modify(
                'this Sunday - ' . (7 - $startOfWeek) . ' days'
            )->startOfDay();
        }
    }

    /**
     * Sets end of the week in the current dateTime
     * 
     * @return self
     */
    public function endOfWeek()
    {
        // 0 = Sunday, 1 = Monday, etc.
        $startOfWeek = intval(get_option('start_of_week'));

        // Calculate the end of the week based on the starting day
        if ($startOfWeek === 0) {
            // Week starts on Sunday, so end is end of Saturday
            return $this->modify('next Saturday')->endOfDay();
        } else {
            // Week starts on Monday, so end is end of Sunday
            return $this->modify('next Sunday')->endOfDay();
        }
    }

    /**
     * Sets start of the day in the current dateTime
     * 
     * @return self
     */
    public function startOfDay()
    {
        return $this->setTime(0, 0, 0, 0);
    }

    /**
     * Sets end of the day in the current dateTime
     * 
     * @return self
     */
    public function endOfDay()
    {
        return $this->setTime(23, 59, 59);
    }

    /**
     * Sets start of the hour in the current DateTime object
     * 
     * @return self
     */
    public function startOfHour()
    {
        return $this->setTime($this->format('H'), 0, 0, 0);
    }

    /**
     * Sets end of the hour in the current DateTime object
     * 
     * @return self
     */
    public function endOfHour()
    {
        return $this->setTime($this->format('H'), 59, 59, 999999);
    }

    /**
     * Sets start of the minute in the current DateTime object
     * 
     * @return self
     */
    public function startOfMinute()
    {
        $hour = $this->format('H');
        $minute = $this->format('i');
        return $this->setTime($hour, $minute, 0, 0);
    }

    /**
     * Sets end of the minute in the current DateTime object
     * 
     * @return self
     */
    public function endOfMinute()
    {
        $hour = $this->format('H');
        $minute = $this->format('i');
        return $this->setTime($hour, $minute, 59, 999999);
    }

    /**
     * Check if the current instance is a weekend.
     *
     * @return bool
     */
    public function isWeekend()
    {
        // Get the day of the week: 0 (Sunday) to 6 (Saturday)
        $dayOfWeek = (int) $this->format('w');
        return ($dayOfWeek === 0 || $dayOfWeek === 6);
    }

    /**
     * Check if the current instance is a weekday.
     *
     * @return bool
     */
    public function isWeekday()
    {
        return !$this->isWeekend();
    }

    /**
     * Check if the current instance is in the past.
     *
     * @return bool
     */
    public function isPast()
    {
        // Compare with current date and time
        return $this < new static();
    }

    /**
     * Check if the current instance is in the future.
     *
     * @return bool
     */
    public function isFuture()
    {
        return $this > new static();
    }

    /**
     * Check if the current instance is the same day as another DateTime instance.
     *
     * @param DateTime $other
     * @return bool
     */
    public function isSameDay(DateTime $other)
    {
        return $this->format('Y-m-d') === $other->format('Y-m-d');
    }

    /**
     * Clone the current Object
     * 
     * @return \NinjaTables\Framework\Support\DateTime
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * Get the difference in years
     * 
     * @param  \NinjaTables\Framework\Support\DateTime $date
     * @return int
     */
    public function diffInYears($date)
    {
        return $this->diff($date)->y;
    }

    /**
     * Get the difference in months
     * 
     * @param  \NinjaTables\Framework\Support\DateTime $date
     * @return int
     */
    public function diffInMonths($date)
    {
        $diff = $this->diff($date);

        return $diff->y  * 12 + $diff->m;
    }

    /**
     * Get the difference in days
     * 
     * @param  \NinjaTables\Framework\Support\DateTime $date
     * @return int
     */
    public function diffInDays($date)
    {
        $diff = $this->diff($date);

        return $diff->days;
    }

    /**
     * Get the difference in hours
     * 
     * @param  \NinjaTables\Framework\Support\DateTime $date
     * @return int
     */
    public function diffInHours($date)
    {
        $diff = $this->diff($date);

        $diffInHours = $diff->h;

        return $diffInHours + $diff->days * 24;
    }

    /**
     * Get the difference in minutes
     * 
     * @param  \NinjaTables\Framework\Support\DateTime $date
     * @return int
     */
    public function diffInMinutes($date)
    {
        $diff = $this->diff($date);

        $diffInMinutes = $diff->i;

        $diffInMinutes += $diff->h * 60;

        return $diffInMinutes + $diff->days * 24 * 60;
    }

    /**
     * Get the difference in seconds
     * 
     * @param  \NinjaTables\Framework\Support\DateTime $date
     * @return int
     */
    public function diffInSeconds($date)
    {
        $diff = $this->diff($date);

        $diffInSeconds = $diff->days * 24 * 60 * 60;

        $diffInSeconds += $diff->h * 60 * 60;

        $diffInSeconds += $diff->i * 60;

        return $diffInSeconds + $diff->s;
    }

    /**
     * Get human friendly time difference (2 hours ago/ 2 hours from now)
     * 
     * @param  \DateTime|string|timestamp $from The datetime to compare from
     * @param  \DateTime|string|timestamp $to The datetime to compare to

     * @return string Human readable string, ie. 5 days ago/from now
     */
    public function diffForHumans($from = null, $to = null)
    {
        // Use the current object's timestamp if $from (and $to) is null
        // This is because ORM's datetime field can call it without params.
        if (is_null($from)) {
            $from = $this->getTimestamp();
        } elseif ($from instanceof \DateTime) {
            $from = $from->getTimestamp();
        } elseif (!is_numeric($from)) {
            $from = (new \DateTime($from))->getTimestamp();
        }

        // Use the current time as $to if not provided
        if (is_null($to)) {
            $to = time();
        } elseif ($to instanceof \DateTime) {
            $to = $to->getTimestamp();
        } elseif (!is_numeric($to)) {
            $to = (new \DateTime($to))->getTimestamp();
        }

        // Calculate the difference in seconds
        $diffInSeconds = abs($to - $from);
        $dateTimeDiff = human_time_diff($from, $to);

        // Determine if the difference is in the past or future
        if ($from > $to) {
            // The "from" time is earlier than "to" (future)
            return sprintf(__('%s from now'), $dateTimeDiff);
        } else {
            // The "from" time is later than "to" (older)
            if ($diffInSeconds > 60) {
                return sprintf(__('%s ago'), $dateTimeDiff);
            }

            // If difference is less than 1 minute, return just now
            return __('just now');
        }
    }

    /**
     * Given a date in the timezone of the site, returns that date in UTC.
     *
     * Requires and returns a date in the Y-m-d H:i:s format.
     * 
     * Return format can be overridden using the $format parameter.
     *
     * @param string $dateString The date to be converted, in the timezone of the site.
     * @param string $format The format string for the returned date. Default 'Y-m-d H:i:s'.
     * @see https://developer.wordpress.org/reference/functions/get_gmt_from_date/
     * 
     * @return string Formatted version of the date, in UTC.
     */
    public function toUTC($dateString, $format = 'Y-m-d H:i:s')
    {
        return get_gmt_from_date($dateString, $format);
    }

    /**
     * Return the ISO-8601 string
     *
     * @see https://stackoverflow.com/a/11173072/741747
     *
     * @return mixed
     */
    public function toJSON()
    {
        return date('c', $this->getTimestamp());
    }

    /**
     * Returns the formatted string
     * 
     * @return string
     */
    public function toString()
    {
        return (string) $this;
    }

    /**
     * Return only the date part as string
     * 
     * @return string
     */
    public function toDateString()
    {
        return (string) $this->format('Y-m-d');
    }

    /**
     * Return only the time part as string
     * 
     * @return string
     */
    public function toTimeString()
    {
        return (string) $this->format('H:i:s');
    }

    /**
     * Returns the formatted string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->format($this->getDateFormat());
    }

    /**
     * Getter to get an unit of DateTime
     * @param  string $key
     * @return string|null
     */
    public function __get($key)
    {
        if ($key == 'year') {
            return $this->format('Y');
        } elseif ($key == 'month') {
            return $this->format('m');
        } elseif ($key == 'day') {
            return $this->format('d');
        } elseif ($key == 'hour') {
            return $this->format('H');
        } elseif ($key == 'minute') {
            return $this->format('i');
        } elseif ($key == 'second') {
            return $this->format('s');
        }
    }

    /**
     * Setter to set an unit of DateTime
     * @param  string $key
     * @param  string|int $value
     * @return self
     */
    public function __set($key, $value)
    {
        if ($key == 'year') {
            return $this->setDate($value, $this->format('m'), $this->format('d'));
        } elseif ($key == 'month') {
            return $this->setDate($this->format('Y'), $value, $this->format('d'));
        } elseif ($key == 'day') {
            return $this->setDate($this->format('Y'), $this->format('m'), $value);
        } elseif ($key == 'hour') {
            return $this->setTime($value, $this->format('i'), $this->format('s'));
        } elseif ($key == 'minute') {
            return $this->setTime($this->format('H'), $value, $this->format('s'));
        } elseif ($key == 'second') {
           return $this->setTime($this->format('H'), $this->format('i'), $value);
        }
    }

    /**
     * Handle Dynamic calls (add/sub)
     * 
     * @param  string $method
     * @param  array $params
     * @return self
     */
    public function __call($method, $params)
    {
        // Dynamic Setter/Getter
        if (strpos($method, 'set') === 0) {
            $unit = strtolower(substr($method, 3));
            if ($params && in_array($unit, static::$singularUnits)) {
                $this->{$unit} = reset($params);
                return $this;
            }
        } elseif (strpos($method, 'get') === 0) {
            $unit = strtolower(substr($method, 3));
            if (in_array($unit, static::$singularUnits)) {
                return $this->{$unit};
            }
        }

        // Dynamic adder/subtractor
        if (strpos($method, 'add') === 0) {
            $action = '+';
        } elseif (strpos($method, 'sub') === 0) {
            $action = '-';
        }

        if (isset($action) && in_array($action, ['+', '-'])) {

            if (!$params) {
                $duration = 1;
            } else {
                $duration = reset($params);
            }


            $unit = strtolower(substr($method, 3));

            $units = array_merge(static::$singularUnits, static::$pluralUnits);

            if (in_array($unit, $units)) {
                return $this->modify("{$action}{$duration}{$unit}");
            }
        }

        throw new InvalidArgumentException("Call to undefined method {$method}.");
    }
}
