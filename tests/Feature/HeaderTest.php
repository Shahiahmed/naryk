<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('ships the burger button and the menu it controls', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('id="burger"')
        ->and($html)->toContain('id="site-menu"')
        ->and($html)->toContain('aria-controls="site-menu"')
        ->and($html)->toContain('aria-expanded="false"');
});

it('keeps the menu links in the markup, so it works without javascript', function () {
    $this->get('/about')
        ->assertOk()
        ->assertSee('/category/qarzhy', escape: false)
        ->assertSee('Қаржы', escape: false);
});

it('falls back to the freedom broker artwork before anyone sets a sponsor', function () {
    expect(Setting::get('sponsor', 'logo'))->toBeNull();

    $sponsor = $this->get('/about')->viewData('sponsor');

    expect($sponsor['logo'])->toEndWith('/img/broker.svg')
        ->and($sponsor['wide'])->toEndWith('/img/broker-wide.svg')
        ->and($sponsor['url'])->toContain('fbroker.kz')
        ->and($sponsor['title'])->toBe('Freedom Broker');
});

it('marks the sponsor link as an ad and opens it in a new tab', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('rel="noopener sponsored"');
});

it('renders both sponsor shapes, and hides each where the other belongs', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    // The wide lockup rides in the nav strip, the shield in the phone
    // masthead. CSS shows one or the other; both ship in the markup.
    expect($html)->toContain('sponsor--wide')
        ->and($html)->toContain('sponsor--compact')
        ->and($html)->toContain('img/broker-wide.svg')
        ->and($html)->toContain('img/broker.svg');
});

it('puts the sponsor second in the menu', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    $firstLink = strpos($html, 'site-nav__link');
    $sponsor = strpos($html, 'sponsor--wide');
    $secondLink = strpos($html, 'site-nav__link', $firstLink + 1);

    expect($sponsor)->toBeGreaterThan($firstLink)
        ->and($sponsor)->toBeLessThan($secondLink);
});

it('lets the admin panel replace both sponsor shapes at once', function () {
    Setting::persist([
        'sponsor' => [
            'logo' => 'logo.svg',
            'url' => 'https://example.com/?utm_source=naryk',
            'title' => 'Демо-спонсор',
        ],
    ]);

    $sponsor = $this->get('/about')->viewData('sponsor');

    expect($sponsor['logo'])->toContain('assets/logo.svg')
        ->and($sponsor['wide'])->toContain('assets/logo.svg')
        ->and($sponsor['url'])->toBe('https://example.com/?utm_source=naryk')
        ->and($sponsor['title'])->toBe('Демо-спонсор');
});

it('scales the wide sponsor logo, rather than pinning it to a 1920x1080 canvas', function () {
    $svg = file_get_contents(public_path('img/broker-wide.svg'));

    // The file the old site shipped declared the whole 1920x1080 artboard, so
    // the logo drew tiny in one corner.
    expect($svg)->not->toContain('viewBox="0 0 1920 1080"')
        ->and($svg)->toContain('viewBox="261 318 1432 449"');
});

it('uses the wordmark the client supplied', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('img/logo-desktop.png');
});

it('swaps in a phone wordmark that is legible on the white masthead', function () {
    $phone = public_path('img/logo-phone.png');
    $html = $this->get('/about')->assertOk()->getContent();

    if (! file_exists($phone)) {
        expect($html)->not->toContain('logo-phone.png');

        return;
    }

    expect($html)->toContain('logo-phone.png');

    /*
     * The client drew this one white, for a dark header. On our white masthead
     * it would have been invisible, so it was recoloured to the green of the
     * desktop wordmark. Guard both failures: a blank file, and one that is too
     * pale to read.
     */
    [$ink, $luminance] = wordmarkInk($phone);

    expect($ink)->toBeGreaterThan(0, 'public/img/logo-phone.png is blank');
    expect($luminance)->toBeLessThan(200, 'public/img/logo-phone.png is too light for a white header');
});

/**
 * @return array{0: int, 1: float} opaque pixel count, and their mean luminance
 */
function wordmarkInk(string $path): array
{
    $image = imagecreatefrompng($path);
    $width = imagesx($image);
    $height = imagesy($image);
    $ink = 0;
    $sum = 0.0;

    for ($x = 0; $x < $width; $x += max(1, (int) ($width / 80))) {
        for ($y = 0; $y < $height; $y += max(1, (int) ($height / 80))) {
            $rgba = imagecolorat($image, $x, $y);
            $alpha = ($rgba >> 24) & 0x7F;

            // 0 is opaque, 127 is fully transparent.
            if ($alpha > 40) {
                continue;
            }

            $ink++;
            $sum += 0.2126 * (($rgba >> 16) & 0xFF)
                + 0.7152 * (($rgba >> 8) & 0xFF)
                + 0.0722 * ($rgba & 0xFF);
        }
    }

    imagedestroy($image);

    return [$ink, $ink > 0 ? $sum / $ink : 255.0];
}

it('serves the sponsor logo with a viewBox, so it scales', function () {
    $svg = file_get_contents(public_path('img/broker.svg'));

    // The file the old site shipped has width and height but no viewBox,
    // which pins it at 1569px wide.
    expect($svg)->toContain('viewBox="0 0 1569 1790"');
});
