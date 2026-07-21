@php
    /** @var \App\Models\Post $post */
    use App\Support\Icons;

    $url = urlencode(url($post->url()));
    $title = urlencode($post->post_title);

    /*
     * Point 4: all five, in the order they hold in the header and the footer.
     *
     * Only Telegram and Facebook have a share endpoint. Instagram, TikTok and
     * Threads have none, so those three open the paper's own account instead of
     * pretending to share the article. The rest comes from `$socials`, the same
     * list the header and footer draw, so the two sets cannot drift apart.
     */
    $shareable = [
        'telegram' => "https://t.me/share/url?url={$url}&text={$title}",
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
    ];

    $labels = [
        'telegram' => 'Telegram',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'threads' => 'Threads',
        'facebook' => 'Facebook',
    ];

    $links = [];

    foreach (Icons::ORDER as $network) {
        if ($link = $shareable[$network] ?? $socials[$network] ?? null) {
            $links[$network] = $link;
        }
    }
@endphp

<div class="share {{ $modifier ?? '' }}">
    @foreach ($links as $network => $link)
        <a class="share__link share__link--{{ $network }}"
           href="{{ $link }}"
           target="_blank"
           rel="noopener noreferrer"
           title="{{ $labels[$network] }}"
           aria-label="{{ $labels[$network] }}">
            @include('site.partials.icon', ['name' => $network])
        </a>
    @endforeach
</div>
