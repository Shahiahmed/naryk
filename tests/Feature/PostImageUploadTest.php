<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function imageAdmin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('uploads a wide cover and stores the path without the images prefix', function () {
    $author = imageAdmin();
    $slug = 'test-wide-'.uniqid();

    Livewire::actingAs($author)
        ->test(CreatePost::class)
        ->fillForm([
            'post_title' => 'Тест горизонтальная',
            'post_name' => $slug,
            'post_content' => '<p>Текст</p>',
            'show_image' => Post::LAYOUT_WIDE,
            'post_image' => [UploadedFile::fake()->image('wide.jpg', 1200, 675)],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('post_name', $slug)->firstOrFail();

    // The column holds `2026/07/abc.jpg`, not `images/2026/07/abc.jpg`.
    expect($post->post_image)->not->toBeNull()
        ->and($post->post_image)->not->toStartWith('images/')
        ->and($post->post_image)->toMatch('#^\d{4}/\d{2}/#');

    expect(Storage::disk('public')->exists(Post::imagePath($post->post_image)))->toBeTrue();
});

it('uploads a tall cover and keeps the vertical layout', function () {
    $author = imageAdmin();
    $slug = 'test-tall-'.uniqid();

    Livewire::actingAs($author)
        ->test(CreatePost::class)
        ->fillForm([
            'post_title' => 'Тест вертикальная',
            'post_name' => $slug,
            'post_content' => '<p>Текст</p>',
            'show_image' => Post::LAYOUT_TALL,
            'post_image' => [UploadedFile::fake()->image('tall.jpg', 900, 1200)],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('post_name', $slug)->firstOrFail();

    expect(DB::table('posts')->where('id', $post->id)->value('show_image'))->toBe('vertical')
        ->and($post->layout())->toBe(Post::LAYOUT_TALL)
        ->and($post->hasImage())->toBeTrue();
});

it('keeps the cover when a post is edited without touching the upload', function () {
    $post = Post::published()->whereNotNull('post_image')->newest()->firstOrFail();
    $before = $post->post_image;

    Livewire::actingAs(imageAdmin())
        ->test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['post_title' => $post->post_title.' (ред.)'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->post_image)->toBe($before);
});

it('replaces the cover of an existing post', function () {
    $post = Post::published()->whereNotNull('post_image')->newest()->firstOrFail();
    $before = $post->post_image;

    Livewire::actingAs(imageAdmin())
        ->test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['post_image' => [UploadedFile::fake()->image('new.jpg', 1200, 675)]])
        ->call('save')
        ->assertHasNoFormErrors();

    $after = $post->fresh()->post_image;

    expect($after)->not->toBe($before)
        ->and($after)->not->toStartWith('images/');

    expect(Storage::disk('public')->exists(Post::imagePath($after)))->toBeTrue();
});
