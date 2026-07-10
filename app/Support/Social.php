<?php

namespace App\Support;

/**
 * The social_media settings mix full URLs with bare handles and a phone
 * number, and the unused ones hold NULL or a literal `?`.
 */
class Social
{
    protected const BASE = [
        'facebook' => 'https://www.facebook.com/',
        'twitter' => 'https://twitter.com/',
        'instagram' => 'https://www.instagram.com/',
        'youtube' => 'https://www.youtube.com/',
        'linkedin' => 'https://www.linkedin.com/in/',
        'telegram' => 'https://t.me/',
        'whatsapp' => 'https://wa.me/',
    ];

    public static function url(string $network, ?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '?' || ! isset(self::BASE[$network])) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if ($network === 'whatsapp') {
            return self::BASE[$network].preg_replace('/\D+/', '', $value);
        }

        return self::BASE[$network].ltrim($value, '@/');
    }

    /**
     * @param  array<string, string|null>  $settings
     * @return array<string, string>
     */
    public static function links(array $settings): array
    {
        $links = [];

        foreach ($settings as $network => $value) {
            if ($url = self::url($network, $value)) {
                $links[$network] = $url;
            }
        }

        return $links;
    }
}
