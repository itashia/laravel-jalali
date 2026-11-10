<?php

declare(strict_types=1);

namespace Jalali\Jalali;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Jalali\Jalali\Calendar\PersianCalendar;
use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\Localization\Digits;
use Jalali\Jalali\Localization\Persian;
use Jalali\Jalali\Parsing\Parser;

final readonly class JalaliDateTime
{
    public int $year;

    public int $month;

    public int $day;

    private function __construct(private DateTimeImmutable $date)
    {
        [$this->year, $this->month, $this->day] = (new PersianCalendar)->fromGregorian((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));
    }

    public static function fromDateTime(DateTimeInterface $date): self
    {
        return new self(DateTimeImmutable::createFromInterface($date));
    }

    public static function create(int $year, int $month, int $day, int $hour = 0, int $minute = 0, int $second = 0, int $microsecond = 0, string $timezone = 'UTC'): self
    {
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59 || $microsecond < 0 || $microsecond > 999999) {
            throw new InvalidDate('Invalid time components.');
        }
        [$gy, $gm, $gd] = (new PersianCalendar)->toGregorian($year, $month, $day);
        $text = sprintf('%04d-%02d-%02d %02d:%02d:%02d.%06d', $gy, $gm, $gd, $hour, $minute, $second, $microsecond);
        $date = new DateTimeImmutable($text, new DateTimeZone($timezone));

        if ($date->format('Y-m-d H:i:s.u') !== $text) {
            throw new InvalidDate('Local time does not exist in this timezone.');
        }

        return new self($date);
    }

    public static function parse(string $input, string $timezone = 'UTC'): self
    {
        $input = Digits::latin($input);

        if (! preg_match('~\A([0-9]{4})/([0-9]{2})/([0-9]{2})\z~D', $input, $parts)) {
            throw new InvalidDate('Expected YYYY/MM/DD with Latin, Persian or Arabic-Indic digits.');
        }

        return self::create((int) $parts[1], (int) $parts[2], (int) $parts[3], timezone: $timezone);
    }

    public function toDateTime(): DateTimeImmutable
    {
        return $this->date;
    }

    public function timezone(string $timezone): self
    {
        return new self($this->date->setTimezone(new DateTimeZone($timezone)));
    }

    public function format(string $format = 'Y/m/d', bool $persianDigits = false): string
    {
        $output = '';
        $escaped = false;
        foreach (str_split($format) as $token) {
            if ($escaped) {
                $output .= $token;
                $escaped = false;

                continue;
            }

            if ($token === '\\') {
                $escaped = true;

                continue;
            }
            $output .= match ($token) {
                'F' => Persian::MONTHS[$this->month],
                'l' => Persian::WEEKDAYS[$this->dayOfWeek()],
                'z' => (string) $this->dayOfYear(),
                't' => (string) $this->daysInMonth(),
                'L' => $this->isLeapYear() ? '1' : '0',
                'Y' => sprintf('%04d', $this->year),
                'm' => sprintf('%02d', $this->month),
                'n' => (string) $this->month,
                'd' => sprintf('%02d', $this->day),
                'j' => (string) $this->day,
                'H', 'i', 's', 'u', 'h', 'g', 'G', 'a', 'A', 'P', 'O', 'e', 'U' => $this->date->format($token),
                default => preg_match('/[A-Za-z]/', $token) === 1 ? throw new InvalidDate("Unsupported format token: $token") : $token,
            };
        }

        if ($escaped) {
            throw new InvalidDate('Trailing format escape.');
        }

        return $persianDigits ? Digits::persian($output) : $output;
    }

    public function formatGregorian(string $format): string
    {
        return $this->date->format($format);
    }

    public function addDays(int $days): self
    {
        if ($days < -1200000 || $days > 1200000) {
            throw new InvalidDate('Day displacement exceeds supported range.');
        }

        return new self($this->date->modify(sprintf('%+d days', $days)));
    }

    /** Clamp to the final valid day of the destination Jalali month. */
    public function addMonths(int $months): self
    {
        if ($months < -38124 || $months > 38124) {
            throw new InvalidDate('Month displacement exceeds the supported range.');
        }
        $index = ($this->year - 1) * 12 + $this->month - 1 + $months;

        if ($index < 0 || $index >= 3177 * 12) {
            throw new InvalidDate('Result exceeds the supported range.');
        }
        $year = intdiv($index, 12) + 1;
        $month = $index % 12 + 1;

        return self::create($year, $month, min($this->day, (new PersianCalendar)->daysInMonth($year, $month)), (int) $this->date->format('H'), (int) $this->date->format('i'), (int) $this->date->format('s'), (int) $this->date->format('u'), $this->date->getTimezone()->getName());
    }

    public function compare(self $other): int
    {
        return $this->date <=> $other->date;
    }

    public function daysInMonth(): int
    {
        return (new PersianCalendar)->daysInMonth($this->year, $this->month);
    }

    public function isLeapYear(): bool
    {
        return (new PersianCalendar)->isLeapYear($this->year);
    }

    public function dayOfWeek(): int
    {
        return ((int) $this->date->format('w') + 1) % 7;
    }

    public function dayOfYear(): int
    {
        return ($this->month <= 7 ? ($this->month - 1) * 31 : 186 + ($this->month - 7) * 30) + $this->day - 1;
    }

    public function startOfDay(): self
    {
        return new self($this->date->setTime(0, 0));
    }

    public function endOfDay(): self
    {
        return new self($this->date->setTime(23, 59, 59, 999999));
    }

    public function startOfWeek(): self
    {
        return $this->addDays(-$this->dayOfWeek())->startOfDay();
    }

    public function endOfWeek(): self
    {
        return $this->addDays(6 - $this->dayOfWeek())->endOfDay();
    }

    public function startOfMonth(): self
    {
        return $this->addDays(1 - $this->day)->startOfDay();
    }

    public function endOfMonth(): self
    {
        return $this->addDays($this->daysInMonth() - $this->day)->endOfDay();
    }

    public function startOfQuarter(): self
    {
        return $this->startOfMonth()->addMonths(-(($this->month - 1) % 3));
    }

    public function endOfQuarter(): self
    {
        return $this->startOfQuarter()->addMonths(2)->endOfMonth();
    }

    public function startOfYear(): self
    {
        return $this->startOfMonth()->addMonths(1 - $this->month);
    }

    public function endOfYear(): self
    {
        return $this->startOfMonth()->addMonths(12 - $this->month)->endOfMonth();
    }

    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function isBefore(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isAfter(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function diff(self $other): DateInterval
    {
        return $this->date->diff($other->date);
    }

    public function diffInSeconds(self $other): float
    {
        return (float) $other->date->format('U.u') - (float) $this->date->format('U.u');
    }

    public function isPast(self $now): bool
    {
        return $this->isBefore($now);
    }

    public function isFuture(self $now): bool
    {
        return $this->isAfter($now);
    }

    public function isToday(self $now): bool
    {
        return $this->format() === $now->timezone($this->date->getTimezone()->getName())->format();
    }

    public function toCarbon(): CarbonImmutable
    {
        return CarbonImmutable::instance($this->date);
    }

    public static function fromCarbon(CarbonInterface $date): self
    {
        return self::fromDateTime($date);
    }

    public static function fromTimestamp(int $seconds, string $timezone = 'UTC'): self
    {
        return new self((new DateTimeImmutable('@'.$seconds))->setTimezone(new DateTimeZone($timezone)));
    }

    public function addWeeks(int $value): self
    {
        if ($value < -1000000 || $value > 1000000) {
            throw new InvalidDate('Arithmetic displacement exceeds supported limit.');
        }

        return $this->addDays($value * 7);
    }

    public function addYears(int $value): self
    {
        if ($value < -1000000 || $value > 1000000) {
            throw new InvalidDate('Arithmetic displacement exceeds supported limit.');
        }

        return $this->addMonths($value * 12);
    }

    public function addHours(int $value): self
    {
        if ($value < -1000000 || $value > 1000000) {
            throw new InvalidDate('Arithmetic displacement exceeds supported limit.');
        }

        return $this->addSeconds($value * 3600);
    }

    public function addMinutes(int $value): self
    {
        if ($value < -1000000 || $value > 1000000) {
            throw new InvalidDate('Arithmetic displacement exceeds supported limit.');
        }

        return $this->addSeconds($value * 60);
    }

    public function addSeconds(int $seconds): self
    {
        if ($seconds < -100000000000 || $seconds > 100000000000) {
            throw new InvalidDate('Arithmetic displacement exceeds supported limit.');
        }

        return new self($this->date->setTimezone(new DateTimeZone('UTC'))->modify(sprintf('%+d seconds', $seconds))->setTimezone($this->date->getTimezone()));
    }

    public function subDays(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addDays(-$value);
    }

    public function subWeeks(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addWeeks(-$value);
    }

    public function subMonths(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addMonths(-$value);
    }

    public function subYears(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addYears(-$value);
    }

    public function subHours(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addHours(-$value);
    }

    public function subMinutes(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addMinutes(-$value);
    }

    public function subSeconds(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            throw new InvalidDate('Arithmetic displacement overflow.');
        }

        return $this->addSeconds(-$value);
    }

    public static function parseFormat(string $format, string $input, string $timezone = 'UTC'): self
    {
        return Parser::parse($format, $input, $timezone);
    }

    public function diffForHumans(self $reference): string
    {
        return Persian::relative($this, $reference);
    }
}
