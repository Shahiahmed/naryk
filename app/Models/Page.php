<?php

namespace App\Models;

/**
 * A row in `posts` with post_type = page. Everything else — the NOT NULL
 * defaults, the image path, the casts — is inherited.
 */
class Page extends Post
{
    public const TYPE = 'page';
}
