<?php

namespace App\Filament\Concerns;

trait SplitsAdSize
{
    /**
     * `advertisements.size` is a single string like `1000x133`, but the form
     * edits width and height separately, as the old admin did.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        [$data['width'], $data['height']] = array_pad(
            explode('x', (string) ($data['size'] ?? ''), 2),
            2,
            null,
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->packSize($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->packSize($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function packSize(array $data): array
    {
        $width = $data['width'] ?? null;
        $height = $data['height'] ?? null;

        unset($data['width'], $data['height']);

        $data['size'] = filled($width) && filled($height) ? "{$width}x{$height}" : null;

        return $data;
    }
}
