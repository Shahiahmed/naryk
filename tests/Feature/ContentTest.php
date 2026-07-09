<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

function admin(): User
{
    return User::role('superadmin')->firstOrFail();
}

it('lists posts, categories and tags', function () {
    $this->actingAs(admin())->get('/admin/posts')->assertOk();
    $this->actingAs(admin())->get('/admin/categories')->assertOk();
    $this->actingAs(admin())->get('/admin/tags')->assertOk();
});

it('hides pages from the post list', function () {
    expect(Post::count())->toBe(7987)
        ->and(Post::whereKey(3263)->exists())->toBeFalse();

    expect(DB::table('posts')->count())->toBe(7988);
});

it('hides taxonomy rows whose term was deleted', function () {
    expect(Category::count())->toBe(11)
        ->and(Tag::count())->toBe(2281);

    expect(DB::table('term_taxonomies')->where('taxonomy', 'category')->count())->toBe(16);
});

it('resolves the post image path', function () {
    expect(Post::imagePath('2026/07/a.jpeg'))->toBe('images/2026/07/a.jpeg')
        ->and(Post::imagePath('images/2026/07/a.jpeg'))->toBe('images/2026/07/a.jpeg')
        ->and(Post::imagePath(null))->toBeNull();
});

it('reads legacy yes/no columns as booleans', function () {
    $post = Post::where('marquee', 'yes')->firstOrFail();

    expect($post->marquee)->toBeTrue()
        ->and($post->pr_news)->toBeFalse();
});

it('reads the card layout out of show_image', function () {
    $wide = new Post(['show_image' => Post::LAYOUT_WIDE]);
    $tall = new Post(['show_image' => Post::LAYOUT_TALL]);
    $text = new Post(['show_image' => Post::LAYOUT_TEXT]);

    expect($wide->layout())->toBe(Post::LAYOUT_WIDE)
        ->and($tall->layout())->toBe(Post::LAYOUT_TALL)
        ->and($text->layout())->toBe(Post::LAYOUT_TEXT);

    // One legacy row holds `public`, one holds NULL. Neither is a layout.
    expect((new Post(['show_image' => 'public']))->layout())->toBe(Post::LAYOUT_WIDE)
        ->and((new Post)->layout())->toBe(Post::LAYOUT_WIDE);
});

it('writes booleans back as the words the column uses', function () {
    $post = Post::latest('id')->firstOrFail();

    $post->marquee = true;
    $post->pr_news = false;
    $post->save();

    $raw = DB::table('posts')->where('id', $post->id)->first();

    expect($raw->marquee)->toBe('yes')
        ->and($raw->pr_news)->toBe('0');
});

it('creates a post through the form with legacy defaults', function () {
    $author = admin();

    Livewire::actingAs($author)
        ->test(CreatePost::class)
        ->fillForm([
            'post_title' => 'Тестовый заголовок',
            'post_name' => 'testovyi-zagolovok-'.uniqid(),
            'post_content' => '<p>Текст</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::latest('id')->firstOrFail();

    expect($post->post_title)->toBe('Тестовый заголовок')
        ->and($post->post_author)->toBe($author->id)
        ->and($post->post_status)->toBe('publish')
        ->and($post->post_visibility)->toBe('public');

    // NOT NULL columns without a default would otherwise reject the insert.
    $raw = DB::table('posts')->where('id', $post->id)->first();
    expect($raw->post_type)->toBe('post')
        ->and($raw->meta_description)->toBe('')
        ->and($raw->post_guid)->toBe('')
        ->and($raw->post_mime_type)->toBe('');
});

it('does not detach categories when the tags of a post are saved', function () {
    $post = Post::has('categories')->has('tags')->latest('id')->firstOrFail();
    $categoryIds = $post->categories()->pluck('term_taxonomies.id')->all();
    $keptTag = $post->tags()->pluck('term_taxonomies.id')->first();

    expect($categoryIds)->not->toBeEmpty();

    Livewire::actingAs(admin())
        ->test(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['tags' => [$keptTag]])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->load('categories', 'tags');

    expect($post->categories->pluck('id')->all())->toBe($categoryIds)
        ->and($post->tags->pluck('id')->all())->toBe([$keptTag]);
});

it('creates a category together with its term', function () {
    $slug = 'test-kategoriya-'.uniqid();

    Livewire::actingAs(admin())
        ->test(CreateCategory::class)
        ->fillForm(['name' => 'Тестовая категория', 'slug' => $slug])
        ->call('create')
        ->assertHasNoFormErrors();

    $category = Category::whereHas('term', fn ($q) => $q->where('slug', $slug))->firstOrFail();

    expect($category->taxonomy)->toBe('category')
        ->and($category->name)->toBe('Тестовая категория')
        ->and($category->term)->toBeInstanceOf(Term::class);
});

it('renames a category through its term', function () {
    $category = Category::firstOrFail();
    $original = $category->term->name;

    Livewire::actingAs(admin())
        ->test(EditCategory::class, ['record' => $category->getKey()])
        ->fillForm(['name' => 'Переименовано', 'slug' => $category->term->slug])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->term->fresh()->name)->toBe('Переименовано')
        ->and($original)->not->toBe('Переименовано');
});
