<?php

namespace App\Http\Controllers;

use App\Mail\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function show()
    {
        return view('pages.apoio-cliente');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:5', 'max:4000'],
        ]);

        try {
            Mail::to(config('mail.admin_address'))->send(new SupportMessage($data));
        } catch (\Throwable $e) {
            Log::error('Envoi message apoio cliente échoué : '.$e->getMessage());
        }

        return back()->with('sent', true);
    }
}
