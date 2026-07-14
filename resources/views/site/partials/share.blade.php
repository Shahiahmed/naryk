@php
    /** @var \App\Models\Post $post */
    $url = urlencode(url($post->url()));
    $title = urlencode($post->post_title);

    /*
     * Point 16: the same networks, in the same order, as everywhere else.
     * TikTok and Threads have no share endpoint, so they drop out here.
     */
    $links = [
        'telegram' => "https://t.me/share/url?url={$url}&text={$title}",
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
        'whatsapp' => "https://api.whatsapp.com/send?text={$title}%20{$url}",
    ];

    $labels = [
        'telegram' => 'Telegram',
        'facebook' => 'Facebook',
        'whatsapp' => 'WhatsApp',
    ];
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
