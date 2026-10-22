<?php

namespace App\Support;

/**
 * Iframe embeds in post bodies.
 *
 * The admin's rich editor (TipTap) has no iframe node: it drops `<iframe>` and
 * `<embed>` the moment a post is opened, so saving any legacy post with a video
 * quietly deleted the video. And code pasted into the editor became escaped
 * text — the reader saw `<iframe src=...>` as words.
 *
 * So the database keeps plain `<iframe>` HTML (the site prints it as is), and
 * only the editor sees each one as an "embed" custom block:
 *
 *   database ──toEditor()──▶ editor ──fromEditor()──▶ database
 *
 * Pasted code is recognised on save, and render() repairs posts that were
 * saved with the escaped code before this existed.
 *
 * Every iframe is rebuilt from its src and height alone, never copied through,
 * so pasted code cannot bring along onload= or similar.
 */
final class Embeds
{
    public const BLOCK_ID = 'embed';

    /** Hosts that serve a video player: those get a 16:9 frame, height ignored. */
    private const VIDEO_HOSTS = [
        'youtube.com', 'youtube-nocookie.com', 'youtu.be', 'vimeo.com', 'player.vimeo.com',
        'rutube.ru', 'vk.com', 'vkvideo.ru', 'ok.ru', 'dzen.ru',
    ];

    /** Our own domains: their pages are served same-origin, so they can auto-size. */
    private const OWN_HOSTS = ['naryk.kz', 'www.naryk.kz'];

    /**
     * Escaped `<iframe ...></iframe>` sitting in the text, optionally as the
     * only content of a paragraph. Quotes may come through as `&quot;`.
     */
    private const ESCAPED_CODE = '~(?:<p\b[^>]*>\s*)?&lt;(iframe|embed)\b((?:(?!&gt;).)*)&gt;(?:\s*&lt;/\1&gt;)?(?:\s*</p>)?~isu';

    /**
     * Reads an embed out of whatever an editor pasted: iframe or embed code,
     * escaped or not, or a bare link.
     *
     * @return array{src: string, height: ?int}|null
     */
    public static function parse(?string $code): ?array
    {
        $code = trim(html_entity_decode((string) $code, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($code === '') {
            return null;
        }

        if (preg_match('~<(?:iframe|embed)\b([^>]*)>~i', $code, $tag)) {
            $attributes = self::attributes($tag[1]);
            $src = $attributes['src'] ?? '';
            $height = $attributes['height'] ?? null;
        } else {
            $src = $code;
            $height = null;
        }

        $src = self::normaliseSrc($src);

        if ($src === null) {
            return null;
        }

        return ['src' => $src, 'height' => self::normaliseHeight($height)];
    }

    /**
     * The markup stored in the database and printed on the site.
     *
     * @param  array{src?: mixed, height?: mixed}  $config
     */
    public static function iframe(array $config): string
    {
        $src = self::normaliseSrc((string) ($config['src'] ?? ''));

        if ($src === null) {
            return '';
        }

        $height = self::normaliseHeight($config['height'] ?? null);
        $attributes = [
            'src' => $src,
            'loading' => 'lazy',
            'allowfullscreen' => 'allowfullscreen',
            'referrerpolicy' => 'strict-origin-when-cross-origin',
        ];

        if (self::isVideo($src)) {
            $modifier = 'embed--video';
            $attributes['allow'] = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        } else {
            $modifier = 'embed--page';

            if ($height !== null) {
                $attributes['height'] = (string) $height;
            } elseif (str_starts_with($src, '/')) {
                // Same-origin: site.js grows the frame to fit its content.
                $attributes['data-autoheight'] = '1';
            }
        }

        $html = '';
        foreach ($attributes as $name => $value) {
            $html .= ' '.$name.'="'.e($value).'"';
        }

        return '<figure class="embed '.$modifier.'"><iframe'.$html.'></iframe></figure>';
    }

    /**
     * Database HTML → editor HTML: each embed becomes a custom block the
     * editor keeps, instead of a tag it throws away.
     */
    public static function toEditor(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        // Ours, with the wrapper, first — so the bare-iframe pass below does
        // not leave an empty <figure> behind.
        $html = preg_replace_callback(
            '~<figure\b[^>]*class="[^"]*\bembed\b[^"]*"[^>]*>\s*(<iframe\b[^>]*>.*?</iframe>)\s*</figure>~isu',
            fn (array $m): string => self::blockFor($m[1]) ?? $m[0],
            $html,
        );

        $html = preg_replace_callback(
            '~<iframe\b[^>]*>.*?</iframe>|<embed\b[^>]*/?>~isu',
            fn (array $m): string => self::blockFor($m[0]) ?? $m[0],
            $html,
        );

        return self::replaceEscapedCode($html, fn (array $embed): string => self::block($embed));
    }

    /**
     * Editor HTML → database HTML: custom blocks and code pasted as text
     * both become real iframes.
     */
    public static function fromEditor(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $html = preg_replace_callback(
            '~<div\b[^>]*data-type="customBlock"[^>]*>\s*</div>~isu',
            function (array $m): string {
                $attributes = self::attributes($m[0]);

                if (($attributes['data-id'] ?? null) !== self::BLOCK_ID) {
                    return $m[0];
                }

                $config = json_decode($attributes['data-config'] ?? '', true);

                return is_array($config) ? self::iframe($config) : '';
            },
            $html,
        );

        return self::replaceEscapedCode($html, fn (array $embed): string => self::iframe($embed));
    }

    /**
     * For the site: posts saved with the code as text still show the frame.
     * Everything else is printed exactly as stored.
     */
    public static function render(?string $html): string
    {
        return self::replaceEscapedCode((string) $html, fn (array $embed): string => self::iframe($embed));
    }

    public static function isVideo(string $src): bool
    {
        $host = strtolower((string) parse_url(str_starts_with($src, '//') ? 'https:'.$src : $src, PHP_URL_HOST));

        foreach (self::VIDEO_HOSTS as $video) {
            if ($host === $video || str_ends_with($host, '.'.$video)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{src: string, height: ?int}  $embed
     */
    private static function block(array $embed): string
    {
        return '<div data-type="customBlock" data-id="'.self::BLOCK_ID.'" data-config="'
            .e(json_encode($embed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)).'"></div>';
    }

    private static function blockFor(string $tag): ?string
    {
        $embed = self::parse($tag);

        return $embed === null ? null : self::block($embed);
    }

    /**
     * @param  callable(array{src: string, height: ?int}): string  $replace
     */
    private static function replaceEscapedCode(string $html, callable $replace): string
    {
        return preg_replace_callback(
            self::ESCAPED_CODE,
            function (array $m) use ($replace): string {
                $embed = self::parse('<'.$m[1].$m[2].'>');

                if ($embed === null) {
                    return $m[0];
                }

                // Code in the middle of a sentence: keep the paragraph around it.
                $opened = (bool) preg_match('~^<p\b~i', $m[0]);
                $closed = (bool) preg_match('~</p>$~i', $m[0]);
                $before = $opened && ! $closed ? preg_replace('~&lt;.*$~isu', '', $m[0]) : '';
                $after = $closed && ! $opened ? '</p>' : '';

                return $before.$replace($embed).$after;
            },
            $html,
        ) ?? $html;
    }

    /**
     * @return array<string, string>
     */
    private static function attributes(string $tag): array
    {
        preg_match_all('~([a-z][a-z0-9:-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))~i', $tag, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $value = $match[2] !== '' ? $match[2] : (($match[3] ?? '') !== '' ? $match[3] : ($match[4] ?? ''));
            $attributes[strtolower($match[1])] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $attributes;
    }

    /**
     * http(s) and protocol-relative links only. Our own pages become
     * root-relative, so they load same-origin on both naryk.kz and www.
     */
    private static function normaliseSrc(string $src): ?string
    {
        $src = trim($src);

        // Already one of ours, made root-relative earlier.
        if (preg_match('~^/(?!/)[^\s"\'<>]*$~', $src)) {
            return $src;
        }

        if (str_starts_with($src, '//')) {
            $src = 'https:'.$src;
        }

        if (! preg_match('~^https?://~i', $src) || filter_var($src, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($src);
        $host = strtolower($parts['host'] ?? '');

        if (in_array($host, self::OWN_HOSTS, true)) {
            return ($parts['path'] ?? '/')
                .(isset($parts['query']) ? '?'.$parts['query'] : '')
                .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        return $src;
    }

    private static function normaliseHeight(mixed $height): ?int
    {
        // "auto", "100%" and the like mean "no fixed height".
        if (! is_numeric($height) && ! (is_string($height) && preg_match('~^\d+\s*px$~i', trim($height)))) {
            return null;
        }

        $height = (int) $height;

        return $height >= 50 && $height <= 5000 ? $height : null;
    }
}
