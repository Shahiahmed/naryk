<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('site.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        /*
         * `website` is a honeypot: a real visitor never sees it, so anything
         * that fills it is a bot. Bounce it as if it succeeded.
         */
        if (filled($request->input('website'))) {
            return back()->with('sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'subject' => ['nullable', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Contact::create($data);

        return back()->with('sent', true);
    }
}
