@php
    /** @var \App\Models\Post $post */
    $url = urlencode(url($post->url()));
    $title = urlencode($post->post_title);

    $links = [
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
        'twitter' => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
        'whatsapp' => "https://api.whatsapp.com/send?text={$title}%20{$url}",
        'telegram' => "https://t.me/share/url?url={$url}&text={$title}",
    ];

    $labels = [
        'facebook' => 'Facebook',
        'twitter' => 'X',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
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
