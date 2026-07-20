<?php

namespace App\Support;

/**
 * The social_media settings mix full URLs with bare handles and a phone
 * number, and the unused ones hold NULL or a literal `?`.
 */
class Social
{
    protected const BASE = [
        'telegram' => 'https://t.me/',
        'instagram' => 'https://www.instagram.com/',
        'tiktok' => 'https://www.tiktok.com/@',
        'threads' => 'https://www.threads.com/@',
        'facebook' => 'https://www.facebook.com/',
        'twitter' => 'https://twitter.com/',
        'youtube' => 'https://www.youtube.com/',
        'linkedin' => 'https://www.linkedin.com/in/',
        'whatsapp' => 'https://wa.me/',
    ];

    /**
     * The accounts the client gave. The settings table has no rows for tiktok
     * or threads and holds `naryk.kz` — not a valid handle — for telegram, so
     * these stand in until someone edits them in the admin panel.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'telegram' => 'https://t.me/narykkz',
        'instagram' => 'https://www.instagram.com/narykkz?igsh=dzdiZDdtczF6aDlk',
        'tiktok' => 'https://www.tiktok.com/@naryk.kz?_r=1&_t=ZS-97ssDY5MmuX',
        'threads' => 'https://www.threads.com/@narykkz?igshid=NTc4MTIwNjQ2YQ==',
        'facebook' => 'https://www.facebook.com/naryk.kz?mibextid=wwXIfr',
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

        /*
         * The dump holds `naryk.kz` as the telegram and twitter handle — the
         * site's own domain, not an account. Building t.me/naryk.kz from it
         * gives a link that leads nowhere, so treat a bare domain as missing
         * and let the account the client gave stand in.
         */
        if ($network !== 'whatsapp' && preg_match('/^[\w-]+\.(kz|com|net|org|io|ru)$/i', $value)) {
            return null;
        }

        if ($network === 'whatsapp') {
            return self::BASE[$network].preg_replace('/\D+/', '', $value);
        }

        return self::BASE[$network].ltrim($value, '@/');
    }

    /**
     * Only the five networks the client listed, in the order they listed them.
     * The old theme showed every row it found, including empty ones.
     *
     * @param  array<string, string|null>  $settings
     * @return array<string, string>
     */
    public static function links(array $settings): array
    {
        $links = [];

        foreach (Icons::ORDER as $network) {
            $url = self::url($network, $settings[$network] ?? null)
                ?? self::DEFAULTS[$network]
                ?? null;

            if ($url) {
                $links[$network] = $url;
            }
        }

        return $links;
    }
}
