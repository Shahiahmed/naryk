<?php

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use App\Support\Embeds;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

// What an editor actually pasted into post 10271, saved as escaped text.
const PASTED = '<p>&lt;iframe src="https://naryk.kz/7.html" width="100%" class="embeds" height="auto" style="border:0;" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"&gt;&lt;/iframe&gt;</p>';

function embedAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

function editedPost(string $content): Post
{
    $post = Post::published()->newest()->firstOrFail();
    DB::table('posts')->where('id', $post->id)->update(['post_content' => $content]);

    return $post->fresh();
}

it('reads a link and height out of pasted code, escaped or not', function () {
    expect(Embeds::parse('<iframe src="https://www.youtube.com/embed/abc" height="315"></iframe>'))
        ->toBe(['src' => 'https://www.youtube.com/embed/abc', 'height' => 315])
        ->and(Embeds::parse('&lt;iframe src=&quot;//www.youtube.com/embed/abc&quot;&gt;'))
        ->toBe(['src' => 'https://www.youtube.com/embed/abc', 'height' => null])
        ->and(Embeds::parse('https://vimeo.com/123'))
        ->toBe(['src' => 'https://vimeo.com/123', 'height' => null]);
});

it('makes our own pages root-relative so they load same-origin', function () {
    expect(Embeds::parse('<iframe src="https://naryk.kz/7.html" height="auto">'))
        ->toBe(['src' => '/7.html', 'height' => null])
        ->and(Embeds::iframe(['src' => '/7.html']))->toContain('data-autoheight="1"');
});

it('refuses anything that is not an http link', function () {
    expect(Embeds::parse('<iframe src="javascript:alert(1)"></iframe>'))->toBeNull()
        ->and(Embeds::parse('просто текст'))->toBeNull();
});

it('rebuilds the iframe instead of copying the pasted attributes', function () {
    $html = Embeds::fromEditor('<p>&lt;iframe src="https://www.youtube.com/embed/abc" onload="alert(1)"&gt;&lt;/iframe&gt;</p>');

    expect($html)->toContain('<iframe src="https://www.youtube.com/embed/abc"')
        ->and($html)->toContain('embed--video')
        ->and($html)->not->toContain('onload')
        ->and($html)->not->toContain('<p>');
});

it('shows code that was saved as text as a real frame on the site', function () {
    $post = editedPost('<p>Алдында</p>'.PASTED.'<p>Кейін</p>');

    $this->get($post->url())
        ->assertOk()
        ->assertSee('<iframe src="/7.html"', escape: false)
        ->assertDontSee('&lt;iframe', escape: false)
        ->assertSee('Кейін');
});

it('keeps a legacy iframe through opening and saving in the admin', function () {
    $post = editedPost('<p>Видео:</p><iframe src="https://www.youtube.com/embed/3os2dpENKQ0" width="560" height="315"></iframe><p>Соңы</p>');

    Livewire::actingAs(embedAdmin())
        ->test(EditPost::class, ['record' => $post->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    $saved = DB::table('posts')->where('id', $post->id)->value('post_content');

    expect($saved)->toContain('<iframe src="https://www.youtube.com/embed/3os2dpENKQ0"')
        ->and($saved)->toContain('Соңы')
        ->and($saved)->not->toContain('customBlock');
});

it('keeps a legacy <embed> too, as an iframe', function () {
    $post = editedPost('<p>Мәтін</p><embed type="text/html" class="embed-html" src="https://naryk.kz/7.html" width="100%" height="auto">');

    Livewire::actingAs(embedAdmin())
        ->test(EditPost::class, ['record' => $post->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(DB::table('posts')->where('id', $post->id)->value('post_content'))
        ->toContain('<iframe src="/7.html"');
});

it('turns code pasted into the editor into an iframe on save', function () {
    $post = editedPost('<p>Мәтін</p>');

    Livewire::actingAs(embedAdmin())
        ->test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['post_content' => '<p>Мәтін</p>'.PASTED])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(DB::table('posts')->where('id', $post->id)->value('post_content'))
        ->toContain('<iframe src="/7.html"')
        ->not->toContain('&lt;iframe');
});
