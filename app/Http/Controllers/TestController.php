<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    // Mot de passe pour accéder au formulaire d'inscription
    private $registrationAccessCode = "3hcig2023";

    public function index()
    {
        return view('index');
    }

    public function verifyRegistrationAccess(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string'
        ]);

        if ($request->access_code === $this->registrationAccessCode) {
            // Stocker dans la session que l'accès est autorisé avec timestamp
            session([
                'registration_access_granted' => true,
                'registration_access_time' => now()->timestamp
            ]);
            return redirect()->route('register')->with('success', 'Accès autorisé');
        }

        return redirect()->route('index')->with('error', 'Code d\'accès incorrect');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }
}
