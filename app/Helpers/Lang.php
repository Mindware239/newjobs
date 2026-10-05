<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Multilingual text for the public registration pages.
 *
 * Every string is rendered in all enabled languages at once, each in its own <span class="t-xx">,
 * and CSS shows the language(s) the visitor picked (default: Hindi + English). Switching language
 * therefore never reloads the page or loses form input, and search engines index every language.
 *
 * Regional languages: drop a file resources/lang/regional/{code}.php returning
 *   ['_name' => 'বাংলা', 'English source text' => 'translated text', ...]
 * and it appears in the switcher automatically. Missing strings fall back to English.
 */
class Lang
{
    public const COOKIE = 'jl_lang';

    private static ?array $regional = null;

    /** Inline text, e.g. labels: "हिंदी / English". */
    public static function t(string $hi, string $en): string
    {
        $html = '<span class="t-hi" lang="hi">' . self::e($hi) . '</span>'
            . '<span class="t-en" lang="en">' . self::e($en) . '</span>';

        foreach (self::regional() as $code => $dict) {
            $html .= '<span class="t-' . $code . '" lang="' . $code . '">' . self::e($dict[$en] ?? $en) . '</span>';
        }

        return $html;
    }

    /** Block text, e.g. headings/paragraphs: secondary language shown on its own line. */
    public static function b(string $hi, string $en): string
    {
        return '<span class="t-blk">' . self::t($hi, $en) . '</span>';
    }

    /** Plain text for places that cannot hold markup (<option>, attributes, emails). */
    public static function plain(string $hi, string $en): string
    {
        return $hi === $en ? $hi : $hi . ' / ' . $en;
    }

    /** Current mode from cookie: "both", "hi", "en" or an enabled regional code. */
    public static function mode(): string
    {
        $mode = (string)($_COOKIE[self::COOKIE] ?? 'both');
        $allowed = array_merge(['both', 'hi', 'en'], array_keys(self::regional()));
        return in_array($mode, $allowed, true) ? $mode : 'both';
    }

    /** code => native name, for the switcher. */
    public static function languages(): array
    {
        $langs = ['both' => 'हिंदी + English', 'hi' => 'हिंदी', 'en' => 'English'];
        foreach (self::regional() as $code => $dict) {
            $langs[$code] = (string)($dict['_name'] ?? strtoupper($code));
        }
        return $langs;
    }

    public static function regional(): array
    {
        if (self::$regional !== null) {
            return self::$regional;
        }

        self::$regional = [];
        $dir = __DIR__ . '/../../resources/lang/regional';
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            $code = basename($file, '.php');
            if (preg_match('/^[a-z]{2,3}$/', $code) && is_array($dict = require $file)) {
                self::$regional[$code] = $dict;
            }
        }

        return self::$regional;
    }

    private static function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
