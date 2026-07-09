<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Messages from the site's contact form. Empty until the front end is built.
 */
#[Fillable(['name', 'email', 'subject', 'message', 'status'])]
class Contact extends Model
{
    protected $attributes = [
        'status' => 'unread',
    ];

    public function markAsRead(): void
    {
        if ($this->status !== 'read') {
            $this->update(['status' => 'read']);
        }
    }
}
