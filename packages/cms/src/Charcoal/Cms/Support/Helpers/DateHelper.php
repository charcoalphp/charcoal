<?php

namespace Charcoal\Cms\Support\Helpers;

use DateTime;
use DateTimeInterface;
use Exception;
use IntlDateFormatter;
// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

/**
 * Class DateHelper
 */
class DateHelper
{
    use TranslatorAwareTrait;

    /**
     * @var DateTime $from
     */
    protected $from;

    /**
     * @var DateTime $to
     */
    protected $to;

    /**
     * @var array $dateFormats The date formats options from config.
     */
    protected $dateFormats;

    /**
     * @var array $timeFormats The time formats options from config.
     */
    protected $timeFormats;

    /**
     * @var string $dateFormat The format from dateFormats to use for the date
     */
    protected $dateFormat;

    /**
     * @var string $dateFormat The format from dateFormats to use for the time
     */
    protected $timeFormat;

    /**
     * DateHelper constructor.
     * @param array $data DateHelper data.
     * @throws Exception When constructor's data missing.
     */
    public function __construct(array $data)
    {
        if (!isset($data['date_formats'])) {
            throw new Exception('date formats configuration must be defined in the DateHelper constructor.');
        }
        if (!isset($data['time_formats'])) {
            throw new Exception('time formats configuration must be defined in the DateHelper constructor.');
        }
        if (!isset($data['translator'])) {
            throw new Exception('Translator needs to be defined in the dateHelper class.');
        }

        $this->setTranslator($data['translator']);
        $this->dateFormats = $data['date_formats'];
        $this->timeFormats = $data['time_formats'];
    }

    /**
     * @param mixed  $date   The date
     *                       [startDate, endDate]
     *                       DateTimeInterface
     *                       string.
     * @param string $format The format to use.
     * @return string
     */
    public function formatDate($date, $format = 'default')
    {
        $this->dateFormat = $format;

        if (is_array($date)) {
            $this->from = $this->parseAsDate($date[0]);
            $this->to = !!($date[1]) ? $this->parseAsDate($date[1]) : null;
        } else {
            $this->from = $this->parseAsDate($date);
            $this->to = null;
        }

        return (string)$this->formatDateFromCase($this->getDateCase());
    }

    /**
     * @param mixed  $date   The date
     *                       [startDate, endDate]
     *                       DateTimeInterface
     *                       string.
     * @param string $format The format to use.
     * @return string
     */
    public function formatTime($date, $format = 'default')
    {
        $this->timeFormat = $format;

        if (is_array($date)) {
            $this->from = $this->parseAsDate($date[0]);
            $this->to = $this->parseAsDate($date[1]);
        } else {
            $this->from = $this->parseAsDate($date);
            $this->to = null;
        }

        return $this->formatTimeFromCase($this->getTimeCase());
    }

    /**
     * Get the usage case by comparing two dates.
     * @return string
     */
    private function getDateCase()
    {
        $from = $this->from;
        $to = $this->to;

        // single date event
        if (!$to || $to->format('Ymd') === $from->format('Ymd')) {
            return 'single';
        }

        $fromDate = [
            'day'   => $from->format('d'),
            'month' => $from->format('m'),
            'year'  => $from->format('y')
        ];

        $toDate = [
            'day'   => $to->format('d'),
            'month' => $to->format('m'),
            'year'  => $to->format('y')
        ];

        $case = null;
        $case = $fromDate['day'] !== $toDate['day'] ? 'different_day' : $case;
        $case = $fromDate['month'] !== $toDate['month'] ? 'different_month' : $case;
        $case = $fromDate['year'] !== $toDate['year'] ? 'different_year' : $case;

        return $case;
    }

    /**
     * Get the usage case by comparing two hours.
     * @return string
     */
    private function getTimeCase()
    {
        $from = $this->from;
        $to = $this->to;

        // Single hour event
        if (!$to || $to->format('Hi') === $from->format('Hi')) {
            if ($to->format('i') == 0) {
                return 'single_round';
            }

            return 'single';
        }

        $fromTime = [
            'hour'   => $from->format('H'),
            'minute' => $from->format('i'),
        ];

        $toTime = [
            'hour'   => $to->format('H'),
            'minute' => $to->format('i'),
        ];

        $case = null;
        $case = $fromTime['hour'] !== $toTime['hour'] ? 'different_time' : $case;
        $case = $fromTime['minute'] == 0 ? 'different_time_round' : $case;
        $case = $fromTime['minute'] != $toTime['minute'] ? 'different_time' : $case;

        return $case;
    }

    /**
     * @param string $case The use case.
     * @return string
     */
    private function formatDateFromCase($case)
    {
        $dateFormats = $this->dateFormats;
        $case = $dateFormats[$this->dateFormat][$case];

        $content = $this->translator()->translation($case['content']);

        $formats['from'] = $this->translator()->translation($case['formats']['from']);
        $formats['to']   = isset($case['formats']['to'])
                           ? $this->translator()->translation($case['formats']['to'])
                           : null;

        $formats['from'] = $this->crossPlatformFormat((string)$formats['from']);
        $formats['to']   = $this->crossPlatformFormat((string)$formats['to']);

        if (!$this->to || !$formats['to']) {
            return sprintf(
                (string)$content,
                $this->formatStrftime($formats['from'], $this->from)
            );
        }

        return sprintf(
            (string)$content,
            $this->formatStrftime($formats['from'], $this->from),
            $this->formatStrftime($formats['to'], $this->to)
        );
    }

    /**
     * @param string $case The use case.
     * @return string
     */
    private function formatTimeFromCase($case)
    {
        $timeFormats = $this->timeFormats;
        $case = $timeFormats[$this->timeFormat][$case];

        $content = $this->translator()->translation($case['content']);

        $formats['from'] = $case['formats']['from'];
        $formats['to'] = isset($case['formats']['to']) ? $case['formats']['to'] : null;

        $formats['from'] = $this->translator()->translation($formats['from']);
        $formats['to'] = $this->translator()->translation($formats['to']);

        if (!$this->to || !$formats['to']) {
            return sprintf(
                (string)$content,
                $this->formatStrftime($formats['from'], $this->from)
            );
        }

        return sprintf(
            (string)$content,
            $this->formatStrftime($formats['from'], $this->from),
            $this->formatStrftime($formats['to'], $this->to)
        );
    }

    /**
     * @param mixed $date The date to convert.
     * @return DateTimeInterface
     */
    private function parseAsDate($date)
    {
        if ($date instanceof DateTimeInterface) {
            return $date;
        }

        return new DateTime($date);
    }

    /**
     * Format a date with a strftime() pattern without calling strftime().
     *
     * strftime() was deprecated in PHP 8.1 and removed in PHP 8.4.
     * Configured date/time formats still use strftime tokens (e.g. %b, %d, %k).
     *
     * @param string            $format A strftime() format string.
     * @param DateTimeInterface $date   The date to format.
     * @return string
     */
    private function formatStrftime($format, DateTimeInterface $date)
    {
        $format = str_replace('%%', "\x00", $format);

        $formatted = preg_replace_callback(
            '/%[#\-]?[a-zA-Z]/',
            function ($match) use ($date) {
                return $this->replaceStrftimeSpecifier($match[0], $date);
            },
            $format
        );

        return str_replace("\x00", '%', $formatted);
    }

    /**
     * @param string            $specifier A strftime() specifier, including the leading %.
     * @param DateTimeInterface $date      The date to format.
     * @return string
     */
    private function replaceStrftimeSpecifier($specifier, DateTimeInterface $date)
    {
        switch ($specifier) {
            case '%a':
                return $this->formatIntl($date, 'EEE', $date->format('D'));
            case '%A':
                return $this->formatIntl($date, 'EEEE', $date->format('l'));
            case '%b':
            case '%h':
                return $this->formatIntl($date, 'MMM', $date->format('M'));
            case '%B':
                return $this->formatIntl($date, 'MMMM', $date->format('F'));
            case '%d':
                return $date->format('d');
            case '%e':
                return sprintf('%2d', (int)$date->format('j'));
            case '%#d':
            case '%-d':
                return $date->format('j');
            case '%m':
                return $date->format('m');
            case '%y':
                return $date->format('y');
            case '%Y':
                return $date->format('Y');
            case '%H':
                return $date->format('H');
            case '%k':
                return sprintf('%2d', (int)$date->format('G'));
            case '%I':
                return $date->format('h');
            case '%l':
                return sprintf('%2d', (int)$date->format('g'));
            case '%M':
                return $date->format('i');
            case '%S':
                return $date->format('s');
            case '%p':
                return $date->format('A');
            case '%P':
                return $date->format('a');
            default:
                return $specifier;
        }
    }

    /**
     * @param DateTimeInterface $date     The date to format.
     * @param string            $pattern  An ICU date pattern.
     * @param string            $fallback A DateTime::format() fallback when intl is unavailable.
     * @return string
     */
    private function formatIntl(DateTimeInterface $date, $pattern, $fallback)
    {
        if (!class_exists(IntlDateFormatter::class)) {
            return $fallback;
        }

        $formatter = new IntlDateFormatter(
            $this->translator()->getLocale(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $date->getTimezone(),
            IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $formatted = $formatter->format($date);
        return ($formatted !== false) ? $formatted : $fallback;
    }

    /**
     * @param mixed $format DateTime to be formatted.
     * @return mixed
     */
    private function crossPlatformFormat($format)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }

        return $format;
    }
}
