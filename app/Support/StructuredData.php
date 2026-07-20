<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Schema.org payloads for the public site. Google and AI crawlers read JSON-LD;
 * that is the format they recommend over HTML microdata attributes.
 */
final class StructuredData
{
    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public static function newsArticle(Post $post, array $settings): array
    {
        $siteName = (string) ($settings['site_information']['company_name']
            ?? $settings['site_information']['sitename']
            ?? 'Naryk.kz');

        $absoluteUrl = url($post->url());
        $description = $post->seoDescription();
        $authorName = filled($post->author?->name) ? (string) $post->author->name : $siteName;

        $payload = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $absoluteUrl,
            ],
            'headline' => $post->post_title,
            'description' => $description,
            'datePublished' => $post->created_at?->toAtomString(),
            'dateModified' => ($post->updated_at ?? $post->created_at)?->toAtomString(),
            'author' => [
                '@type' => 'Person',
                'name' => $authorName,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => self::publisherLogoUrl($settings),
                ],
            ],
            'inLanguage' => 'kk',
            'isAccessibleForFree' => true,
        ];

        if ($post->hasImage()) {
            $payload['image'] = [self::absoluteUrl((string) $post->imageUrl())];
        }

        $category = $post->categories->first();
        if ($category !== null) {
            $payload['articleSection'] = $category->name;
        }

        $keywords = $post->tags->pluck('name')->filter()->values()->all();
        if ($keywords !== []) {
            $payload['keywords'] = implode(', ', $keywords);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public static function publisherLogoUrl(array $settings): string
    {
        foreach (['ogimage', 'logowebsite', 'favicon'] as $key) {
            $path = Setting::assetPath($settings['logo_image'][$key] ?? null);
            if ($path !== null) {
                return self::absoluteUrl(Storage::disk('public')->url($path));
            }
        }

        if (file_exists(public_path('img/logo-desktop.png'))) {
            return self::absoluteUrl(asset('img/logo-desktop.png'));
        }

        return url('/');
    }

    public static function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }
}
