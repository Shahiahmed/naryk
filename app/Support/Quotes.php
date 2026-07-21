<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The ticker on the old site read https://apps.naryk.kz/get-sum straight from
 * the browser. Fetching it here instead keeps the ticker in the HTML (no flash
 * of empty prices) and survives the endpoint being down.
 */
class Quotes
{
    /**
     * @return array{time: ?string, items: array<int, array{ticker: string, last: string, status: string}>}
     */
    public function get(): array
    {
        $payload = Cache::remember('naryk.quotes', config('naryk.quotes.ttl'), function (): array {
            try {
                $response = Http::timeout(5)->acceptJson()->get(config('naryk.quotes.endpoint'));

                return $response->successful() ? $response->json() : [];
            } catch (\Throwable $e) {
                Log::warning('Quotes endpoint unreachable: '.$e->getMessage());

                return [];
            }
        });

        return [
            'time' => $payload['time'] ?? null,
            'items' => $this->order($payload['kase'] ?? []),
        ];
    }

    /**
     * Logos were lifted from the old theme. Kazatomprom's is an .ico, and FRHC
     * has none yet — the ticker renders its code alone.
     */
    public static function logo(string $ticker): ?string
    {
        foreach (['png', 'ico', 'svg', 'jpg'] as $extension) {
            if (file_exists(public_path("img/stock/{$ticker}.{$extension}"))) {
                return asset("img/stock/{$ticker}.{$extension}");
            }
        }

        return null;
    }

    /**
     * @param  array<string, array{last?: string, status?: string}>  $kase
     * @return array<int, array{ticker: string, last: string, status: string, currency: ?string}>
     */
    protected function order(array $kase): array
    {
        $items = [];
        $labels = config('naryk.quotes.labels', []);
        $currencies = config('naryk.quotes.currency', []);

        foreach (config('naryk.quotes.order') as $key) {
            if (! isset($kase[$key]['last'])) {
                continue;
            }

            $items[] = [
                'ticker' => $labels[$key] ?? $key,
                'last' => $kase[$key]['last'],
                'status' => strtoupper($kase[$key]['status'] ?? ''),
                'currency' => $currencies[$key] ?? null,
            ];
        }

        return $items;
    }
}
