<?php

namespace App\Http\Controllers;

use App\Models\FoundationLetter;
use Illuminate\Http\Request;

class PublicLetterVerificationController extends Controller
{
    public function verify($hash)
    {
        $letter = FoundationLetter::where('signature_hash', $hash)->first();

        if (!$letter) {
            return view('public.letters.verify', [
                'isValid' => false,
                'letter' => null,
                'hash' => $hash,
            ]);
        }

        return view('public.letters.verify', [
            'isValid' => true,
            'letter' => $letter,
            'hash' => $hash,
        ]);
    }
}
