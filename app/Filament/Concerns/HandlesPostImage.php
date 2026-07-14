<?php

namespace App\Filament\Concerns;

use App\Models\Post;
use Illuminate\Support\Str;

/**
 * `posts.post_image` stores the path without its `images/` prefix, but the
 * FileUpload works in paths relative to the disk root, so the two have to be
 * translated on the way in and out.
 *
 * This used to live in formatStateUsing/dehydrateStateUsing on the field. That
 * ran inside Filament's state pipeline, where an unexpected type is coerced
 * rather than raised — and the upload was silently dropped on the server while
 * every test passed. Here the data is a plain array, so a wrong value is
 * visible rather than swallowed.
 */
trait HandlesPostImage
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['post_image'] = Post::imagePath($data['post_image'] ?? null);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->packPostImage($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->packPostImage($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function packPostImage(array $data): array
    {
        if (! array_key_exists('post_image', $data)) {
            return $data;
        }

        $image = $data['post_image'];

        // A single FileUpload can still hand back an array of one.
        if (is_array($image)) {
            $image = reset($image) ?: null;
        }

        $data['post_image'] = filled($image)
            ? Str::after((string) $image, 'images/')
            : null;

        return $data;
    }
}
