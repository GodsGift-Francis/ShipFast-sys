<?php

namespace App\Support;

class Country
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim(preg_replace('/\s+/', ' ', $value)));

        return match ($value) {
            'us','usa','u.s.','u.s.a.','united states of america' => 'united states',
            'gb','gbr','uk','u.k.','great britain' => 'united kingdom',
            'ng','nga' => 'nigeria','ca','can' => 'canada','gh','gha' => 'ghana',
            'de','deu' => 'germany','fr','fra' => 'france','cn','chn' => 'china',
            'ae','are','uae' => 'united arab emirates','au','aus' => 'australia','in','ind' => 'india',default => $value
        };
    }

    public static function international(string $origin, string $destination): bool
    {
        return self::normalize($origin) !== self::normalize($destination);
    }
}
