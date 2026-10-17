<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function create()
    {
        return view('public.contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:4000'],
            'company_website' => ['nullable', 'string', 'max:200'],
        ]);

        if (filled($data['company_website'] ?? null)) {
            return back()->with('status', 'Votre message a été pris en compte.');
        }

        $message = ContactMessage::query()->create(collect($data)->except('company_website')->all());

        try {
            Mail::raw($message->message, function ($mail) use ($message) {
                $mail->to(config('mail.from.address'))
                    ->replyTo($message->email, $message->name)
                    ->subject('Contact CVora — '.$message->subject);
            });
        } catch (\Throwable) {
            // Le message reste enregistré même si l'envoi de courriel échoue.
        }

        return back()->with('status', 'Merci. Votre message a été enregistré.');
    }
}
