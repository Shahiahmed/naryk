<?php

namespace App\Filament\Concerns;

trait EditsTerm
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $term = $this->getRecord()->term;

        $data['name'] = $term?->name;
        $data['slug'] = $term?->slug;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->getRecord()->term?->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);

        unset($data['name'], $data['slug']);

        return $data;
    }
}
