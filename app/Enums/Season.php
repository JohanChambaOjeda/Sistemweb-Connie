<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

/**
 * Peak seasons of the farm. Prices may be set per season.
 */
enum Season: string
{
    case Valentines = 'valentines';
    case MothersDay = 'mothers_day';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return __('prices.seasons.'.$this->value);
    }

    /**
     * The season a date falls into, using the ranges in config/floriculture.php.
     */
    public static function forDate(Carbon $date): ?self
    {
        /** @var array<string, array{from: string, to: string}> $ranges */
        $ranges = config('floriculture.seasons', []);

        foreach ($ranges as $season => $range) {
            $case = self::tryFrom($season);

            if ($case === null) {
                continue;
            }

            $from = Carbon::createFromFormat('m-d', $range['from'])->setYear($date->year)->startOfDay();
            $to = Carbon::createFromFormat('m-d', $range['to'])->setYear($date->year)->endOfDay();

            if ($date->betweenIncluded($from, $to)) {
                return $case;
            }
        }

        return null;
    }
}
