<?php

declare(strict_types=1);

namespace Jalali\Jalali\Parsing;

use Jalali\Jalali\Exceptions\InvalidDate;
use Jalali\Jalali\JalaliDateTime;
use Jalali\Jalali\Localization\Digits;

final class Parser
{
    public static function parse(string $format, string $input, string $timezone = 'UTC'): JalaliDateTime
    {
        $patterns = ['Y' => '[0-9]{4}', 'm' => '[0-9]{2}', 'd' => '[0-9]{2}', 'H' => '[0-9]{2}', 'i' => '[0-9]{2}', 's' => '[0-9]{2}', 'u' => '[0-9]{6}'];
        $regex = '';
        $seen = [];
        $escaped = false;
        foreach (str_split($format) as $token) {
            if ($escaped) {
                $regex .= preg_quote($token, '~');
                $escaped = false;
            } elseif ($token === '\\') {
                $escaped = true;
            } elseif (isset($patterns[$token])) {
                if (isset($seen[$token])) {
                    throw new InvalidDate('Repeated parser token.');
                }
                $seen[$token] = true;
                $regex .= '(?P<'.$token.'>'.$patterns[$token].')';
            } elseif (preg_match('/[A-Za-z]/', $token) === 1) {
                throw new InvalidDate('Unsupported parser token: '.$token);
            } else {
                $regex .= preg_quote($token, '~');
            }
        }

        if ($escaped || ! isset($seen['Y'], $seen['m'], $seen['d'])) {
            throw new InvalidDate('A parser format requires Y, m and d and no dangling escape.');
        }

        if (! preg_match('~\A'.$regex.'\z~D', Digits::latin($input), $parts)) {
            throw new InvalidDate('Input does not match the requested format.');
        }

        return JalaliDateTime::create((int) $parts['Y'], (int) $parts['m'], (int) $parts['d'], (int) ($parts['H'] ?? 0), (int) ($parts['i'] ?? 0), (int) ($parts['s'] ?? 0), (int) ($parts['u'] ?? 0), $timezone);
    }
}
