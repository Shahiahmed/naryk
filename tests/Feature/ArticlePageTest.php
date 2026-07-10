<?php

use App\Models\AdPlacement;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

function anArticle(): Post
{
    return Post::published()->has('categories')->has('tags')->newest()->firstOrFail();
}

it('opens an article by its slug under the news prefix', function () {
    $post = anArticle();

    $this->get($post->url())
        ->assertOk()
        ->assertSee($post->post_title, escape: false)
        ->assertSee('Соңғы жаңалықтар');

    expect($post->url())->toStartWith('/news/');
});

it('404s on an unknown slug and on a draft', function () {
    $this->get('/news/eshtene-joq')->assertNotFound();

    $post = anArticle();
    $post->update(['post_status' => 'draft']);

    $this->get($post->url())->assertNotFound();
});

it('hides a private article', function () {
    $post = anArticle();
    $post->update(['post_visibility' => 'private']);

    $this->get($post->url())->assertNotFound();
});

it('counts a view without touching updated_at', function () {
    $post = anArticle();
    $hits = $post->post_hits;
    $updatedAt = DB::table('posts')->where('id', $post->id)->value('updated_at');

    $this->get($post->url())->assertOk();

    $row = DB::table('posts')->where('id', $post->id)->first();

    expect($row->post_hits)->toBe($hits + 1)
        ->and($row->updated_at)->toBe($updatedAt);
});

it('picks the article banner slot from ads_show', function () {
    $post = anArticle();

    expect($post->bannerPlacement())->toBe('single-720');

    $post->ads_show = 'iworld';
    expect($post->bannerPlacement())->toBe('its-world');

    $post->ads_show = 'hide';
    expect($post->bannerPlacement())->toBeNull();
});

it('shows no banner in an article that hides ads', function () {
    $post = anArticle();
    $post->update(['ads_show' => 'hide']);

    expect($this->get($post->url())->viewData('banner'))->toBeNull();
});

it('shows the single-720 banner by default', function () {
    $post = anArticle();
    $post->update(['ads_show' => null]);

    $banner = $this->get($post->url())->viewData('banner');

    expect($banner?->name)->toBe('Single-720');
});

it('leaves the banner out when its placement is switched off', function () {
    AdPlacement::where('slug', 'single-720')->update(['active' => 'n']);

    expect(AdPlacement::bannerFor('single-720'))->toBeNull();
});

it('links the tags of an article to their pages', function () {
    $post = anArticle();
    $tag = $post->tags->first();

    $this->get($post->url())->assertSee($tag->url(), escape: false);

    expect($tag->url())->toBe('/tag/'.$tag->term->slug);
});

it('lists the posts of a category', function () {
    $category = Category::has('posts')->firstOrFail();

    $this->get($category->url())
        ->assertOk()
        ->assertSee($category->name, escape: false)
        ->assertSee('Санаттар');

    expect($category->url())->toBe('/category/'.$category->term->slug);
});

it('paginates a category', function () {
    $category = Category::withCount('posts')
        ->orderByDesc('posts_count')
        ->first();

    $posts = $this->get($category->url())->viewData('posts');

    expect($posts->perPage())->toBe(15)
        ->and($posts->total())->toBeGreaterThan(15);

    $this->get($category->url().'?page=2')->assertOk();
});

it('lists the posts of a tag', function () {
    $tag = anArticle()->tags->first();

    $this->get($tag->url())
        ->assertOk()
        ->assertSee('Тегтер');
});

it('404s on an unknown category or tag', function () {
    $this->get('/category/joq-sanat')->assertNotFound();
    $this->get('/tag/joq-teg')->assertNotFound();
});

it('does not let a category page shadow the article route', function () {
    $post = anArticle();

    // /category/{slug} is registered before news/{slug}.
    $this->get('/category/'.$post->post_name)->assertNotFound();
});
