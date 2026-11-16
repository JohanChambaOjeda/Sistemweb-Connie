<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Exposes the lang/<locale> PHP files to the front end, so React components
 * never carry hardcoded Spanish or English strings.
 */
class Translations
{
    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    /**
     * @return array<string, mixed>
     */
    public static function forLocale(string $locale): array
    {
        if (isset(self::$cache[$locale])) {
            return self::$cache[$locale];
        }

        $path = lang_path($locale);

        if (! File::isDirectory($path)) {
            return self::$cache[$locale] = [];
        }

        $translations = [];

        foreach (File::files($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            /** @var array<string, mixed> $group */
            $group = require $file->getRealPath();
            $translations[$file->getFilenameWithoutExtension()] = $group;
        }

        return self::$cache[$locale] = $translations;
    }

    public static function flush(): void
    {
        self::$cache = [];
    }

    /**
     * Locales the interface is translated into.
     *
     * @return list<string>
     */
    public static function available(): array
    {
        return ['es', 'en'];
    }
}
