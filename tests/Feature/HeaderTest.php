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

it('falls back to the freedom broker logo before anyone sets a sponsor', function () {
    expect(Setting::get('sponsor', 'logo'))->toBeNull();

    $sponsor = $this->get('/about')->viewData('sponsor');

    expect($sponsor['logo'])->toEndWith('/img/broker.svg')
        ->and($sponsor['url'])->toContain('fbroker.kz')
        ->and($sponsor['title'])->toBe('Freedom Broker');
});

it('marks the sponsor link as an ad and opens it in a new tab', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('rel="noopener sponsored"')
        ->and($html)->toContain('class="sponsor"');
});

it('lets the admin panel replace the sponsor', function () {
    Setting::persist([
        'sponsor' => [
            'logo' => 'logo.svg',
            'url' => 'https://example.com/?utm_source=naryk',
            'title' => 'Демо-спонсор',
        ],
    ]);

    $sponsor = $this->get('/about')->viewData('sponsor');

    expect($sponsor['logo'])->toContain('assets/logo.svg')
        ->and($sponsor['url'])->toBe('https://example.com/?utm_source=naryk')
        ->and($sponsor['title'])->toBe('Демо-спонсор');
});

it('uses the wordmark the client supplied', function () {
    $html = $this->get('/about')->assertOk()->getContent();

    expect($html)->toContain('img/logo-desktop.png');
});

it('offers the phone wordmark only once it holds anything', function () {
    $phone = public_path('img/logo-phone.png');
    $html = $this->get('/about')->assertOk()->getContent();

    if (! file_exists($phone)) {
        expect($html)->not->toContain('logo-phone.png');

        return;
    }

    // The file the client first sent was blank — transparent and white, not a
    // single coloured pixel. Rendering it would leave the phone header empty.
    $image = imagecreatefrompng($phone);
    $width = imagesx($image);
    $height = imagesy($image);
    $hasInk = false;

    for ($x = 0; $x < $width && ! $hasInk; $x += max(1, (int) ($width / 60))) {
        for ($y = 0; $y < $height; $y += max(1, (int) ($height / 60))) {
            $rgba = imagecolorat($image, $x, $y);
            $alpha = ($rgba >> 24) & 0x7F;
            $red = ($rgba >> 16) & 0xFF;
            $green = ($rgba >> 8) & 0xFF;
            $blue = $rgba & 0xFF;

            if ($alpha < 100 && ($red < 240 || $green < 240 || $blue < 240)) {
                $hasInk = true;
                break;
            }
        }
    }

    imagedestroy($image);

    expect($hasInk)->toBeTrue('public/img/logo-phone.png is blank — ask the client to re-export it');
});

it('serves the sponsor logo with a viewBox, so it scales', function () {
    $svg = file_get_contents(public_path('img/broker.svg'));

    // The file the old site shipped has width and height but no viewBox,
    // which pins it at 1569px wide.
    expect($svg)->toContain('viewBox="0 0 1569 1790"');
});
