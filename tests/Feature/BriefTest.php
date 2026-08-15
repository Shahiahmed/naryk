<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Cache::forget('naryk.quotes');

    Http::fake([
        'apps.naryk.kz/*' => Http::response([
            'time' => '2026.07.14 18:00',
            'kase' => ['KSPI' => ['last' => '41400.00', 'status' => 'DOWN']],
        ]),
    ]);
});

it('runs the PR badge inline with the headline, not above it', function () {
    // Point 21.
    $post = Post::published()->newest()->firstOrFail();
    $post->pr_news = true;
    $post->save();

    $html = $this->get('/')->assertOk()->getContent();

    $badge = strpos($html, 'pr-badge');
    $heading = strrpos(substr($html, 0, $badge), '<h2 class="card__title');

    // The badge sits inside the heading, so the title carries on after it.
    expect($heading)->not->toBeFalse()
        ->and($badge - $heading)->toBeLessThan(200);
});

it('drops the time of day from a card', function () {
    // Point 28. The datetime attribute keeps the full stamp — that is what the
    // standard asks for, and it is not shown — so only check the visible text.
    $html = $this->get('/')->assertOk()->getContent();

    $shown = str($html)->after('meta__date')->after('>')->before('</time>')->toString();

    expect($shown)->toMatch('/\d{2}\.\d{2}\.\d{4}/')
        ->and($shown)->not->toMatch('/\d{2}:\d{2}/');
});

it('folds the side columns into the feed for phones', function () {
    // Point 22: banner after the third card, expert opinions after the sixth.
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('feed-block--phone')
        ->and($html)->toContain('Мамандар пікірі');
});

it('insets the ticker rather than bleeding it across the screen', function () {
    // Point 8.
    $this->get('/')->assertOk()->assertSee('class="ticker shell"', escape: false);
});

it('paints the ticker in the green of the wordmark', function () {
    // Point 2.
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('--green-logo: #425e40')
        ->and($css)->toMatch('/\.ticker \{[^}]*background: var\(--green-logo\)/s');
});

it('gives the rubric strip and the ticker the same height', function () {
    // Point 3.
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('--strip-height: 44px')
        ->and($css)->toMatch('/\.site-nav__inner \{[^}]*min-height: var\(--strip-height\)/s')
        ->and($css)->toMatch('/\.ticker \{[^}]*min-height: var\(--strip-height\)/s');
});

it('keeps the ticker marquee from compressing its quotes onto each other', function () {
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toMatch('/\.ticker__track \{[^}]*flex:\s*0 0 auto/s')
        ->and($css)->toMatch('/\.ticker__group \{[^}]*flex:\s*0 0 auto/s')
        ->and($css)->toMatch('/\.ticker__item \{[^}]*flex:\s*0 0 auto/s');
});

it('drives every gap from one spacing scale', function () {
    // Points 3, 9 and 15 all ask for the same rhythm site-wide.
    $css = file_get_contents(public_path('assets/site.css'));

    // Strip the :root block, where the scale itself is defined.
    $rules = preg_replace('/:root \{.*?\}/s', '', $css);

    // No raw pixel gaps or paddings outside the scale.
    preg_match_all('/(?:gap|padding|margin):\s*([^;]+);/', $rules, $matches);

    $raw = array_filter($matches[1], function (string $value): bool {
        return preg_match('/\b\d+px\b/', $value)
            && ! str_contains($value, 'var(')
            && ! preg_match('/^0|auto|1px|2px|3px/', trim($value));
    });

    expect($raw)->toBeEmpty('these gaps bypass the spacing scale: '.implode(' | ', $raw));
});

it('serves Roboto self-hosted and sets it on the body', function () {
    // Point 9: the font the old site used, the files the client sent.
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('@font-face')
        ->and($css)->toContain('Roboto-Regular.woff2')
        ->and($css)->toContain('Roboto-Bold.woff2')
        ->and($css)->toMatch("/font-family: 'Roboto'/");

    expect(file_exists(public_path('fonts/Roboto-Regular.woff2')))->toBeTrue()
        ->and(file_exists(public_path('fonts/Roboto-Bold.woff2')))->toBeTrue();

    // Real woff2 files, not stray HTML error pages.
    expect(substr(file_get_contents(public_path('fonts/Roboto-Regular.woff2')), 0, 4))->toBe('wOF2');
});

it('keeps time in Almaty, where the client writes it', function () {
    /*
     * The publishing hours in the client's table run 09:00-18:00 — a working
     * day in Almaty, and nothing at all in UTC. Laravel's default would have
     * stamped new posts five hours early and dropped them into the wrong place
     * in a feed ordered by date.
     */
    expect(config('app.timezone'))->toBe('Asia/Almaty');

    $post = Post::published()->newest()->firstOrFail();

    expect($post->created_at->timezone->getName())->toBe('Asia/Almaty');
});

it('shows the press address in the footer, and no phone number', function () {
    // Points 25 and 26.
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('press.naryk@gmail.com')
        ->and($html)->not->toContain('tel:')
        ->and($html)->not->toContain('+7 778 112 1332');
});

it('puts Freedom first in the ticker, marked in dollars', function () {
    /*
     * Point 10. The endpoint calls it FRHC_KZ; asking for FRHC matched nothing
     * and the quote quietly dropped out of the strip. Freedom alone is quoted
     * in dollars, so it alone carries the note.
     */
    expect(config('naryk.quotes.order')[0])->toBe('FRHC_KZ')
        ->and(config('naryk.quotes.labels')['FRHC_KZ'])->toBe('FRHC')
        ->and(config('naryk.quotes.currency'))->toBe(['FRHC_KZ' => 'USD']);
});

it('opens the column headings in their own tab', function () {
    /*
     * Point 12: the headings were plain text, with no way into the rubric.
     *
     * Only Мамандар пікірі is asserted against the live page: Арнайы жобалар
     * has no posts in the client's data yet, so its column does not render at
     * all. The heading itself is checked directly, which holds either way.
     */
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('/category/'.config('naryk.columns.expert_opinions').'" target="_blank"');

    $heading = view('site.partials.column-title', [
        'title' => 'Арнайы жобалар',
        'slug' => config('naryk.columns.special_projects'),
    ])->render();

    expect($heading)->toContain('/category/arnayy-jobalar')
        ->and($heading)->toContain('target="_blank"');
});

it('gives Мамандар пікірі enough headlines to scroll, and the phone five', function () {
    /*
     * This went 6 → 9 → 5 → 9 across three briefs. The column scrolls within
     * itself; at five nothing sat under the fold, so the scroll vanished and
     * the client noticed the same day. On a phone the column unfolds into the
     * feed, where only the page scrolls, so there it is trimmed back to five.
     */
    $response = $this->get('/');

    expect($response->viewData('expertOpinions'))->toHaveCount(9);
    expect($response->viewData('specialProjects')->count())->toBeLessThanOrEqual(5);

    $html = $response->getContent();
    $phone = str($html)->after('feed-block--phone')->before('</div>'.PHP_EOL)->toString();

    expect(substr_count($phone, 'aside-card__title'))->toBeLessThanOrEqual(5);
});

it('fills a rubric card that has no picture with the wordmark', function () {
    /*
     * Rubric pages lay their cards on a grid, so a post without a picture left
     * a hole beside neighbours that had one.
     */
    $post = Post::published()->newest()->firstOrFail();
    $post->post_image = null;
    $post->save();

    expect($post->coverUrl())->toEndWith(config('naryk.fallback_image'))
        ->and(file_exists(public_path(config('naryk.fallback_image'))))->toBeTrue();

    $html = view('site.partials.grid-card', ['post' => $post])->render();

    expect($html)->toContain('grid-card__media--fallback')
        ->and($html)->toContain(config('naryk.fallback_image'));
});

it('shares a pictureless post with the wordmark, not a blank slip', function () {
    // Point 5: og:image fell through to the logo on white.
    $post = Post::published()->newest()->firstOrFail();
    $post->post_image = null;
    $post->save();

    $html = $this->get($post->url())->assertOk()->getContent();

    $og = str($html)->after('property="og:image" content="')->before('"')->toString();

    expect($og)->toStartWith('http')
        ->and($og)->toEndWith(config('naryk.fallback_image'));
});

it('leaves the pictureless cards in the home feed alone', function () {
    // "БІРАҚ БАС БЕТТЕ СУРЕТСІЗ МАТЕРИАЛДАР ҚАЗІРГІДЕЙ ТҰРА БЕРСІН"
    $post = Post::published()->newest()->firstOrFail();
    $post->post_image = null;
    $post->save();

    $html = view('site.partials.card', ['post' => $post])->render();

    expect($html)->not->toContain(config('naryk.fallback_image'))
        ->and($html)->not->toContain('card__media');
});

it('keeps every headline on one size', function () {
    // Point 3: the card, grid and column headlines each had their own size.
    $css = file_get_contents(public_path('assets/site.css'));

    $size = fn (string $rule) => str($css)->after($rule)->before('}')->after('font-size:')->before(';')->trim()->toString();

    expect($size('.card__title {'))->toBe('var(--text-base)')
        ->and($size('.grid-card__title {'))->toBe('var(--text-base)')
        ->and($size('.aside-card__title {'))->toBe('var(--text-base)');
});

it('lines the rubric strip up with the cards on a phone', function () {
    /*
     * Point 1: the strip ran wider than the grey cards below it. Its ends now
     * match theirs, and the bars are real elements rather than :before — as a
     * pseudo-element each bar belonged to the rubric after it, so spreading the
     * row jammed every bar against a word.
     */
    $css = file_get_contents(public_path('assets/site.css'));
    $phone = str($css)->after('@media (max-width: 800px)')->toString();

    expect($phone)->toContain('.site-nav .shell')
        ->and($phone)->toContain('padding: 0 var(--space-4);')
        ->and($css)->not->toContain('.site-nav__link + .site-nav__link::before');

    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, 'site-nav__sep'))->toBeGreaterThan(0);
});

it('gives the ticker the same gap above as below, on a phone', function () {
    // Point 2: a full interval above and half of one below left it lopsided.
    $css = file_get_contents(public_path('assets/site.css'));
    $phone = str($css)->after('@media (max-width: 800px)')->toString();

    $ticker = str($phone)->after('.ticker {')->before('}')->toString();
    $columns = str($phone)->after('.columns {')->before('}')->toString();

    expect($ticker)->toContain('margin-top: var(--space-3)')
        ->and($columns)->toContain('padding-top: var(--space-3)');
});

it('carries a logo for Freedom in the ticker, like the rest', function () {
    // Point 4: every other company had one; FRHC showed its code alone.
    expect(App\Support\Quotes::logo('FRHC'))->not->toBeNull();
});

it('overrules the inline font sizes the old editor left in articles', function () {
    /*
     * Point 9: paragraphs came out at different sizes within one article —
     * 3393 posts carry an inline font-size. The content is the client's data,
     * so the styling is overruled rather than the text rewritten.
     */
    $css = file_get_contents(public_path('assets/site.css'));

    expect($css)->toContain('.article__body font')
        ->and(str($css)->after('.article__body font')->before('}')->toString())
        ->toContain('font-size: inherit !important');
});

/*
 * Brief B (03.08) — the five points below.
 */

it('flies the new logo in the masthead', function () {
    // Brief B point 1: logo-desktop.png → logo-new.png.
    expect(file_exists(public_path('img/logo-new.png')))->toBeTrue();

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('img/logo-new.png')
        ->and($html)->not->toContain('img/logo-desktop.png');
});

it('keeps the view count and the date on one line', function () {
    /*
     * Brief B point 3. The count is grouped in threes with a space — "12 345" —
     * which the phone would break onto a second line under the date.
     */
    $css = file_get_contents(public_path('assets/site.css'));
    $meta = str($css)->after('.meta {')->before('}')->toString();

    expect($meta)->toContain('flex-wrap: nowrap')
        ->and($meta)->toContain('white-space: nowrap');
});

it('adds a one-line search to the phone burger: an icon between about and the socials', function () {
    // Brief B point 4.
    $html = $this->get('/')->assertOk()->getContent();

    $menu = strpos($html, 'id="site-menu"');
    $about = strpos($html, 'БІЗ ТУРАЛЫ', $menu);
    $toggle = strpos($html, 'menu-search-toggle', $menu);
    $socials = strpos($html, 'socials--menu', $menu);

    // In the drawer, in order: about, the search icon, then the socials — one row.
    expect($about)->toBeLessThan($toggle)
        ->and($toggle)->toBeLessThan($socials)
        ->and($html)->toContain('id="menu-search"');

    // The icon opens the field as a modal floated over the page, not a box in the row.
    $css = file_get_contents(public_path('assets/site.css'));
    $overlay = str($css)->after('.menu-search {')->before('}')->toString();

    expect($overlay)->toContain('position: fixed');
});

it('holds a post dated in the future out of the feed until its time', function () {
    // Brief B point 5: scheduled publishing rides on created_at, the publish date.
    $post = Post::published()->newest()->firstOrFail();

    $post->created_at = now()->addDay();
    $post->save();

    expect($post->isPublished())->toBeFalse()
        ->and(Post::published()->whereKey($post->getKey())->exists())->toBeFalse();

    $post->created_at = now()->subMinute();
    $post->save();

    expect($post->isPublished())->toBeTrue()
        ->and(Post::published()->whereKey($post->getKey())->exists())->toBeTrue();
});

it('offers an editable publish date in the admin, for scheduling', function () {
    // Brief B point 5: the field the editor sets.
    $this->actingAs(User::role('superadmin')->firstOrFail())
        ->get('/admin/posts/create')
        ->assertOk()
        ->assertSee('Дата и время публикации');
});

it('previews an unpublished material by its link for staff, and hides it from readers', function () {
    // Brief B point 2.
    $post = Post::published()->has('categories')->has('tags')->newest()->firstOrFail();
    $hits = $post->post_hits;

    // A future date takes it out of publication without touching its status.
    $post->created_at = now()->addWeek();
    $post->save();

    // A reader gets a 404 — the material is simply not there.
    $this->get($post->url())->assertNotFound();

    // Staff opens it by the same link, marked as a preview.
    $this->actingAs(User::where('email', 'info@sait.kz')->firstOrFail())
        ->get($post->url())
        ->assertOk()
        ->assertSee('article-preview-note');

    // The preview must not inflate the view count.
    expect($post->fresh()->post_hits)->toBe($hits);
});

it('opens a hidden material for anyone holding its signed share link', function () {
    // Brief B point 2 — shareable link: a logged-out visitor gets in through the
    // signed URL, so an editor can show a draft without handing out an account.
    $post = Post::published()->has('categories')->has('tags')->newest()->firstOrFail();
    $hits = $post->post_hits;

    $post->post_visibility = 'private';
    $post->save();

    // A plain guess of the address is still hidden.
    $this->get($post->url())->assertNotFound();

    // The signed link opens for a guest, marked as a preview, no login needed.
    $this->get($post->previewUrl())
        ->assertOk()
        ->assertSee('article-preview-note');

    // A tampered signature is refused.
    $this->get($post->previewUrl().'x')->assertNotFound();

    // Sharing a preview must not inflate the view count either.
    expect($post->fresh()->post_hits)->toBe($hits);
});

it('shows the share link in the admin only while the post is hidden', function () {
    $staff = User::where('email', 'info@sait.kz')->firstOrFail();

    $hidden = Post::published()->newest()->firstOrFail();
    $hidden->post_visibility = 'private';
    $hidden->save();

    $this->actingAs($staff)
        ->get('/admin/posts/'.$hidden->getKey().'/edit')
        ->assertOk()
        ->assertSee('Ссылка, чтобы показать до публикации');

    // A public, live post has nothing to share — the field stays hidden.
    $live = Post::published()->where('post_visibility', 'public')
        ->whereKeyNot($hidden->getKey())->newest()->firstOrFail();

    $this->actingAs($staff)
        ->get('/admin/posts/'.$live->getKey().'/edit')
        ->assertOk()
        ->assertDontSee('Ссылка, чтобы показать до публикации');
});
