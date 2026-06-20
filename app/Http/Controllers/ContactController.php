<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:20|max:2000',
        ]);

        // TODO Phase 1: simpan ke log / queue email
        // Mail::to(config('mail.from.address'))->send(new ContactMail($validated));

        return back()->with('success', 'Pesan kamu sudah terkirim! Kami akan merespons dalam 1–2 hari kerja.');
    }
}
